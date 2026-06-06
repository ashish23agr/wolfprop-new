<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Carbon\Carbon;
use Corcel\Model\User as User;
use App\Lead;
use DB;
use Auth;
use App\LeadDetail;
use App\LeadAction;
use App\LeadBedroom;
use App\LeadParking;
use App\LeadBathroom;
use App\Bathroom;
use App\Bedroom;
use App\LeadNeighborhood;
use Corcel\Model\Post as Post;
use Corcel\Model\Term as Term;
use Corcel\Model\Option;
use Mail;

//use Corcel\Laravel\Auth\AuthUserProvider;
class LeadsController extends Controller {

    private $siteUrl;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->siteUrl = Option::get('siteurl');
    }

    public function index(Request $request) {
        $startDt = date('F d, Y', strtotime(Carbon::now()->subDays(30)));
        $endtDt = date('F d, Y', strtotime(Carbon::now()));
        if ($request->input('start_date')) {
            $startDt = $request->input('start_date');
        }
        if ($request->input('end_date')) {
            $endtDt = $request->input('end_date');
        }
        $unAssignedLeads = Lead::where('user_id', NULL)->where('is_deleted', 0)->get()->count();
        $allLeads = Lead::where('is_deleted', 0)->get()->count();
        return view('leads.index', compact('unAssignedLeads', 'allLeads', 'startDt', 'endtDt'));
    }

    public function leadsList(Request $request) {

        $users = Lead::with('leadDetail')->where('is_deleted', 0);
        if (Auth::user()->role == 'member')
            $users->where('user_id', '=', Auth::user()->ID);
        if ($request->input('type')) {
            if ($request->input('type') == 'assigned')
                $users->where('user_id', '!=', NULL);

            if ($request->input('type') == 'holding')
                $users->where('user_id', NULL);
        }
        if ($request->start_dt && $request->end_dt) {
            //$users->where('move_in_date', '>=', date('Y-m-d', strtotime($request->start_dt)))->where('move_in_date', '<=', date('Y-m-d', strtotime($request->end_dt)));
        }
        if ($request->lead_status) {
            $users->where('lead_status', $request->lead_status);
        }
        if ($request->user_id) {
            $users->where('user_id', $request->user_id);
        }
        $users = $users->select('*');
        return datatables()->of($users)->addColumn('action', function ($users) {
                        $propertyPlublishedArr = $this->publishedPropertiesIds();
                        /*    bookmarked properties counts start here */
                        $bookmarkedCount = $matchedCount = $deletedCount = 0;

                        $bookmarkedCount = LeadAction::where('user_id', Auth::user()->ID)->where('lead_id', $users->id)->whereIn('property_id', $propertyPlublishedArr)->where(function($query) {
                                            $query->where('action', 1);
                                            $query->orWhere('action', 3);
                                        })->count();
                        $deletedCount = LeadAction::where('user_id', Auth::user()->ID)->where('lead_id', $users->id)->whereIn('property_id', $propertyPlublishedArr)->where(function($query) {
                                            $query->where('action', 2);
                                            $query->orWhere('action', 3);
                                        })->count();

                        /*    matched properties counts start here */
                        $propertyIds = LeadAction::where(['user_id' => Auth::user()->ID, 'lead_id' => $users->id])->where(function($query) {
                                            $query->where('action', 2);
                                            $query->orWhere('action', 3);
                                        })->pluck('property_id');


                        $bedroom_ids = [];
                        $bathroom_ids = [];
                        $neighborhood_ids = [];
                        $parking_ids = [];

                        $lead = Lead::where('id', '=', $users->id)->with('leadDetail', 'leadBedrooms', 'leadBathrooms.bathroomData', 'leadNeighborhood','leadParking')->first();


                        
                        if ($lead->leadBedrooms && count($lead->leadBedrooms) > 0) {
                            foreach ($lead->leadBedrooms as $bedroom) {
                                $bedroom_ids[] = $bedroom->bedroom_id;
                            }
                        }
                        if ($lead->leadBathrooms && count($lead->leadBathrooms) > 0) {
                            foreach ($lead->leadBathrooms as $bathroom) {
                                //$bathroom_ids[] = $bathroom->bathroom_id;
                                $bathroom_ids[] = $bathroom['bathroomData']->bathroom;
                            }
                        }
                        if ($lead->leadNeighborhood && count($lead->leadNeighborhood) > 0) {
                            foreach ($lead->leadNeighborhood as $neighborhood) {
                                $neighborhood_ids[] = $neighborhood->neighborhood_id;
                            }
                        }

                           if ($lead->leadParking && !empty($lead->leadParking) > 0) {
                            foreach ($lead->leadParking as $parking) {
                                //$bathroom_ids[] = $bathroom->bathroom_id;
                                // $parking_ids[] = $parking['parking']->parking;
                                $parking_ids[] = $parking['parking'];
                            }
                        }

                        // echo "<pre>"; print_r($parking_ids); exit;
                           


                        $query = Post::where('post_type', @$lead->leadDetail->category)
                                ->whereNotIn('ID', $propertyIds)
                                ->whereIn('ID', $propertyPlublishedArr)
                                ->select('ID', 'post_title', 'post_type', 'post_author');

                       //  echo "<pre>"; print_r($query->get()->toArray()); exit;
        

                        /*                                     * *OPEN HOUSE */
                        if (isset($lead->leadDetail->category) && $lead->leadDetail->category == '$lead->leadDetail->category' && isset($lead->leadDetail->open_house) && !empty($lead->leadDetail->open_house)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where('meta_key', 'open_house')->where('meta_value', @$lead->leadDetail->open_house);
                                    });
                        }

                        /*                                     * *SOLD UNDER CONTRACT */
                        if (isset($lead->leadDetail->category) && $lead->leadDetail->category == '$lead->leadDetail->category' && isset($lead->leadDetail->sold_under_contract) && !empty($lead->leadDetail->sold_under_contract)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where('meta_key', 'sold_under_contract')->where('meta_value', @$lead->leadDetail->sold_under_contract);
                                    });
                        }

                        /*                                     * *BATHROOM */
                        if ($bathroom_ids && !empty($bathroom_ids)) {
                            $query->whereHas('meta', function($q) use($lead, $bathroom_ids) {
                                        $q->where('meta_key', 'bathrooms')->whereIn('meta_value', @$bathroom_ids);
                                    });
                        }

                        /*                                     * *BEDROOM */
                        if ($bedroom_ids && !empty($bedroom_ids)) {
                            $query->whereHas('meta', function($q) use($lead, $bedroom_ids) {
                                        $q->where('meta_key', 'bedrooms')->whereIn('meta_value', @$bedroom_ids);
                                    });
                        }

                        /*                                     * *NEIGHBORHOOD */
                        /* if ($neighborhood_ids && !empty($neighborhood_ids)) {
                            $query->whereHas('meta', function($q) use($lead, $neighborhood_ids) {
                            $q->where('meta_key', 'neighborhood')->whereIn('meta_value', @$neighborhood_ids);
                            });
                            }
                            */
                        /*                                     * *BUDGET */
                        if (!empty($lead->leadDetail->min_budget) && !empty($lead->leadDetail->max_budget)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where('meta_key', 'listing_price')->whereBetween('meta_value', [@$lead->leadDetail->min_budget, @$lead->leadDetail->max_budget]);
                                    });
                        } else {
                            if (!empty($lead->leadDetail->min_budget)) {
                                $query->whereHas('meta', function($q) use($lead) {
                                            $q->where('meta_key', 'listing_price')->where('meta_value', '>=', $lead->leadDetail->min_budget);
                                        });
                            } else if (!empty($lead->leadDetail->max_budget)) {
                                $query->whereHas('meta', function($q) use($lead) {
                                            $q->where('meta_key', 'listing_price')->where('meta_value', '>=', $lead->leadDetail->max_budget);
                                        });
                            }
                        }



                        /*                                     * *PARKING */
                        /*if (!empty($lead->leadDetail->parking)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'parking',
                                            'meta_value' => @$lead->leadDetail->parking
                                        ]);
                                    });
                        }*/
                        /*                                     * *LAUNDRY */
                        /*if (!empty($lead->leadDetail->laundry)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'laundry',
                                            'meta_value' => @$lead->leadDetail->laundry
                                        ]);
                                    });
                        }*/
                        /*                                     * *PETS */
                        /*if (!empty($lead->leadDetail->pet)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'pets',
                                            'meta_value' => @$lead->leadDetail->pet
                                        ]);
                                    });
                        }*/
                        /*                                     * *CITY */
                        /*if (!empty($lead->leadDetail->city)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'city',
                                            'meta_value' => @$lead->leadDetail->city
                                        ]);
                                    });
                        }*/
                        /*                                     * *STATE */
                        /*if (!empty($lead->leadDetail->state_id)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'state',
                                            'meta_value' => @$lead->leadDetail->state_id
                                        ]);
                                    });
                        }*/
                        /* * *ZIPCODE */
                        if (!empty($lead->leadDetail->zipcode)) {
                            $query->whereHas('meta', function($q) use($lead) {
                                        $q->where([
                                            'meta_key' => 'zip_code',
                                            'meta_value' => @$lead->leadDetail->zipcode
                                        ]);
                                    });
                        }

                        if ($bathroom_ids && !empty($bathroom_ids)) {
                            $query->whereHas('meta', function($q) use($lead, $bathroom_ids) {
                                        $q->where('meta_key', 'bathrooms')->whereIn('meta_value', @$bathroom_ids);
                                    });
                        }

                        if (!empty($parking_ids)) {
                                $query->whereHas('taxonomies', function($q) use($lead) {
                                    $q->where(['taxonomy' => 'parking']);
                                });
                                $query->whereHas('taxonomies.term', function($q) use($parking_ids) {
                                    // $parking_ids = implode(',', $parking_ids);
                                    $q->whereIn('slug' , $parking_ids,
                                   );
                                });
                        }
                        //echo "<pre>"; print_r($parking_ids); exit;

                        
                        $matchedCount = $query->count();
                        /*    matched properties counts end here */

                        $actions = "<a title=\"View Lead\" href=\"" . route('leads.show', ['id' => $users->id]) . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                        $actions .= "<a title=\"Update Lead Detail\" href=\"" . route('leads.edit', ['id' => $users->id]) . "\" class=\"btn btn-xs btn-warning btn-flat info-btn\"><i class=\"glyphicon glyphicon-edit\"></i> Edit</a>&nbsp;";
                        $actions .= "<a title=\"Delete Lead\" onclick=\"return confirm('Are you sure want to delete the Lead?')\" href=\"" . url('delete-lead?id=' . $users->id) . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"glyphicon glyphicon-trash\"></i> Delete</a>&nbsp;";
                        $actions .= "<a title=\"Matches\" href=\"" . url('matched-properties?id=' . $users->id) . "\" class=\"btn btn-xs btn-primary btn-flat info-btn\"><i class=\"fa fa-tasks\"></i> Matches ($matchedCount)</a> &nbsp;";
                        $actions .= "<a title=\"Bookmarked\"  href=\"" . url('bookmarked-properties?lead-id=' . $users->id) . "\" class=\"btn btn-xs btn-warning btn-flat info-btn\"><i class=\"fa fa-bookmark\"></i> Bookmarked ($bookmarkedCount)</a>&nbsp;";
                        $actions .= "<a title=\"Deleted Properties\"  href=\"" . url('deleted-properties?lead-id=' . $users->id) . "\" class=\"btn btn-xs btn-primary btn-flat info-btn\"><i class=\"fa fa-trash\"></i> Deleted Properties($deletedCount)</a>&nbsp;";
                        if ($users->user_id != NULL && Auth::user()->role == 'admin')
                            $actions .= "<a title=\"Remove Agent\" onclick=\"return confirm('Are you sure want to remove the Agent?')\" href=\"" . url('remove-agent?id=' . $users->id) . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"glyphicon glyphicon-remove\"></i> Remove Agent</a> &nbsp;";

                        return $actions;
                    })->addColumn('full_name', function($row) {
                        return ucwords(strtolower($row->first_name . ' ' . $row->last_name));
                    })
            ->filterColumn('full_name', function ($query, $keyword) {
                        $keywords = trim($keyword);
                        $query->whereRaw("CONCAT(first_name, last_name) like ?", ["%{$keywords}%"]);
                    })
            ->make(true);
    }

    public function create(Request $request) {
        $parking_ids = [];
        return view('leads.createOrUpdate',compact('parking_ids'));
    }

    public function store(Request $request) {
        //dd($request->all());
        $request->validate([
            'first_name' => 'required|max:255',
            //'email' => 'required|email',
            'category' => 'required',
            'no_of_bedroom' => 'required',
            'no_of_bathroom' => 'required',
            //'laundry' => 'required',
            //'state_id' => 'required'
        ]);
        DB::beginTransaction();

        try {
            if (Auth::user()->role == 'member') {
                $request->request->add(['user_id' => Auth::user()->ID]); //add request
            }
            $leadinfo = Lead::create($request->all(), []);
            if ($leadinfo) {
                $leadDetail = new LeadDetail;
                $leadDetail->lead_id = $leadinfo->id;
                $leadDetail->category = $request->category;
                $leadDetail->min_budget = $request->min_budget;
                $leadDetail->max_budget = $request->max_budget;
                //$leadDetail->parking = $request->parking;
                //$leadDetail->laundry = $request->laundry;
                $leadDetail->pet = $request->pet;
                //$leadDetail->open_house = $request->open_house;
                //$leadDetail->sold_under_contract = $request->sold_under_contract;
                //$leadDetail->city = $request->city;
                //$leadDetail->state_id = $request->state_id;
                $leadDetail->zipcode = $request->zipcode;
                $leadDetail->save();

                //save bedrooms
                if ($request->no_of_bedroom && !empty($request->no_of_bedroom) && count($request->no_of_bedroom) > 0) {
                    foreach ($request->no_of_bedroom as $bedrooms) {
                        $bedroom = new LeadBedroom;
                        $bedroom->lead_id = $leadinfo->id;
                        $bedroom->bedroom_id = $bedrooms;
                        $bedroom->save();
                    }
                }
                
                //save bathrooms
                if ($request->no_of_bathroom && !empty($request->no_of_bathroom) && count($request->no_of_bathroom) > 0) {
                    foreach ($request->no_of_bathroom as $bathrooms) {
                        $bathroom = new LeadBathroom;
                        $bathroom->lead_id = $leadinfo->id;
                        $bathroom->bathroom_id = $bathrooms;
                        $bathroom->save();
                    }
                }
                //save neighbourhood    
                if ($request->neighborhood && !empty($request->neighborhood) && count($request->neighborhood) > 0) {
                    foreach ($request->neighborhood as $neighborhoods) {
                        $neighborhood = new LeadNeighborhood;
                        $neighborhood->lead_id = $leadinfo->id;
                        $neighborhood->neighborhood_id = $neighborhoods;
                        $neighborhood->save();
                    }
                }

                //save parkings
                if ($request->parking && !empty($request->parking) && count($request->parking) > 0) {
                    foreach ($request->parking as $park) {
                        $bedroom = new LeadParking;
                        $bedroom->lead_id = $leadinfo->id;
                        $bedroom->parking = $park;
                        $bedroom->save();
                    }
                }

                DB::commit();
                return redirect('leads')->with('success', 'Lead created successfully!');
            }
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show($id) {
        $leadDetail = Lead::where('id', '=', $id)->with('leadDetail', 'leadBedrooms', 'leadBathrooms', 'leadNeighborhood','leadParking')->first();
        return view('leads.view', compact('leadDetail'));
    }

    public function edit($id) {
        $bedroom_ids = [];
        $bathroom_ids = [];
        $neighborhood_ids = [];
        $parking_ids = [];
        $leadDetail = Lead::where('id', '=', $id)->with('leadDetail', 'leadBedrooms', 'leadBathrooms', 'leadNeighborhood','leadParking')->first();

        if ($leadDetail->leadBedrooms && count($leadDetail->leadBedrooms) > 0) {
            foreach ($leadDetail->leadBedrooms as $bedroom) {
                $bedroom_ids[] = $bedroom->bedroom_id;
            }
        }
        if ($leadDetail->leadBathrooms && count($leadDetail->leadBathrooms) > 0) {
            foreach ($leadDetail->leadBathrooms as $bathroom) {
                $bathroom_ids[] = $bathroom->bathroom_id;
            }
        }
        if ($leadDetail->leadParking && count($leadDetail->leadParking) > 0) {
            foreach ($leadDetail->leadParking as $park) {
                $parking_ids[] = $park->parking;
            }
        }

        if ($leadDetail->leadNeighborhood && count($leadDetail->leadNeighborhood) > 0) {
            foreach ($leadDetail->leadNeighborhood as $neighborhood) {
                $neighborhood_ids[] = $neighborhood->neighborhood_id;
            }
        }
        return view('leads.createOrUpdate', compact('leadDetail', 'id', 'bedroom_ids', 'bathroom_ids', 'neighborhood_ids','parking_ids'));
    }

    public function update(Request $request, $id) {
        $request->validate([
            'first_name' => 'required|max:255',
            //'email' => 'required|email',
            'category' => 'required',
            'no_of_bedroom' => 'required',
            'no_of_bathroom' => 'required',
            //'laundry' => 'required',
            //'state_id' => 'required'
        ]);
        DB::beginTransaction();
        try {
            $leadUpdate = Lead::findOrFail($id);
            if (Auth::user()->role == 'member') {
                $request->request->add(['user_id' => Auth::user()->ID]); //add request
            }
            $input = $request->all();
            if ($leadUpdate->user_id != $input['user_id']) {
                LeadAction::where('lead_id', $leadUpdate->id)->delete();
            }
            if ($leadUpdate->fill($input)->save()) {

                $open_house = $request->open_house;
                $sold_under_contract = $request->sold_under_contract;
                if ($request->category == 'rental-listing') {
                    $open_house = NULL;
                    $sold_under_contract = NULL;
                }
                LeadDetail::updateOrCreate([
                    'lead_id' => $id,
                        ], [
                    "category" => $request->category,
                    "min_budget" => $request->min_budget,
                    "max_budget" => $request->max_budget,
                    "no_of_bedroom" => $request->no_of_bedroom,
                    "no_of_bathroom" => $request->no_of_bathroom,
                    //"parking" => $request->parking,
                    //"laundry" => $request->laundry,
                    "pet" => $request->pet,
                    //"open_house" => $open_house,
                    //"sold_under_contract" => $sold_under_contract,
                    "neighborhood" => $request->neighborhood,
                    //"city" => $request->city,
                    //"state_id" => $request->state_id,
                    "zipcode" => $request->zipcode,
                ]);
                //save bedrooms
                LeadBedroom::where('lead_id', $id)->delete();
                if ($request->no_of_bedroom && !empty($request->no_of_bedroom) && count($request->no_of_bedroom) > 0) {
                    foreach ($request->no_of_bedroom as $bedrooms) {
                        $bedroom = new LeadBedroom;
                        $bedroom->lead_id = $id;
                        $bedroom->bedroom_id = $bedrooms;
                        $bedroom->save();
                    }
                }
                //save bathrooms
                LeadBathroom::where('lead_id', $id)->delete();
                if ($request->no_of_bathroom && !empty($request->no_of_bathroom) && count($request->no_of_bathroom) > 0) {
                    foreach ($request->no_of_bathroom as $bathrooms) {
                        $bathroom = new LeadBathroom;
                        $bathroom->lead_id = $id;
                        $bathroom->bathroom_id = $bathrooms;
                        $bathroom->save();
                    }
                }
                //save neighbourhood    
                LeadNeighborhood::where('lead_id', $id)->delete();
                if ($request->neighborhood && !empty($request->neighborhood) && count($request->neighborhood) > 0) {
                    foreach ($request->neighborhood as $neighborhoods) {
                        $neighborhood = new LeadNeighborhood;
                        $neighborhood->lead_id = $id;
                        $neighborhood->neighborhood_id = $neighborhoods;
                        $neighborhood->save();
                    }
                }

                //save parkings
                LeadParking::where('lead_id', $id)->delete();
                if ($request->parking && !empty($request->parking) && count($request->parking) > 0) {
                    foreach ($request->parking as $park) {
                        $bedroom = new LeadParking;
                        $bedroom->lead_id = $id;
                        $bedroom->parking = $park;
                        $bedroom->save();
                    }
                }
            }
            DB::commit();
            return redirect('leads')->with('success', 'Leads updated successfully!');
        } catch (\Exception $e) {
            // Rollback Transaction
            DB::rollback();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function removeAgent(Request $request) {
        if ($request->input('id') && !empty($request->input('id')) && Lead::where('id', '=', $request->input('id'))->exists()) {
            if (Lead::where('id', $request->input('id'))->update(['user_id' => NULL])) {
                if (LeadAction::where('lead_id', '=', $request->input('id'))->exists()) {
                    LeadAction::where('lead_id', $request->input('id'))->delete();
                }
                return redirect('leads')->with('success', 'Agent removed successfully!');
            } else {
                return redirect()->back()->with('error', 'Something went wrong. Please try again!');
            }
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again!');
        }
    }

    public function deleteLead(Request $request) {
        if ($request->input('id') && !empty($request->input('id')) && Lead::where('id', '=', $request->input('id'))->exists()) {
            if (Lead::where('id', $request->input('id'))->update(['is_deleted' => 1])) {
                if (LeadAction::where('lead_id', '=', $request->input('id'))->exists()) {
                    LeadAction::where('lead_id', $request->input('id'))->delete();
                }
                return redirect('leads')->with('success', 'Lead deleted successfully!');
            } else {
                return redirect()->back()->with('error', 'Something went wrong. Please try again!');
            }
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again!');
        }
    }

    public function assignLeads(Request $request) {
        $unAssignedLead = collect([]);
        $agents = Lead::where('user_id', NULL)->where('is_deleted', 0)->selectRaw('id, first_name,last_name')->get();
        foreach ($agents as $agent) {
            $unAssignedLead->offsetSet($agent->id, ucwords(strtolower($agent->first_name . ' ' . $agent->last_name)) . " (#" . $agent->id . ")");
        }
        if ($request->isMethod('post')) {
            Lead::whereIn('id', $request->leads)->update(['user_id' => $request->user_id]);
            return redirect('leads')->with('success', 'Leads assigned successfully!');
        }

        return view('leads.assignLeads', compact('unAssignedLead'));
    }

    public function matchedProperties(Request $request) {
        $lead = Lead::where('id', '=', $request->input('id'))->with('leadDetail', 'leadBedrooms', 'leadBathrooms', 'leadNeighborhood','leadParking')->first();

        if ($request->input('id') && !empty($request->input('id')) && Lead::where('id', '=', $request->input('id'))->exists()) {
            return view('leads.matchedProperties', compact('lead'));
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again!');
        }
    }
    public function getMatchedPropertiesIds($lead, $request) {
        $bedroom_ids = [];
        $bathroom_ids = [];
        $neighborhood_ids = [];
        $parking_ids = [];
        $propertyIds = LeadAction::where(['user_id' => Auth::user()->ID, 'lead_id' => $lead->id])->where(function($query) {
                            $query->where('action', 2);
                            $query->orWhere('action', 3);
                        })->pluck('property_id');


        if (isset($lead->leadBedrooms) && !empty($lead->leadBedrooms) && count($lead->leadBedrooms) > 0) {
            foreach ($lead->leadBedrooms as $bedroom) {
                $bedroom_ids[] = $bedroom->bedroom_id;
            }
        }
        if (isset($lead->leadBathrooms) && !empty($lead->leadBathrooms) && count($lead->leadBathrooms) > 0) {
            foreach ($lead->leadBathrooms as $bathroom) {
                //$bathroom_ids[] = $bathroom->bathroom_id;
                $bathroom_ids[] = $bathroom['bathroomData']->bathroom;
            }
        }
        
        if (isset($lead->leadNeighborhood) && !empty($lead->leadNeighborhood) && count($lead->leadNeighborhood) > 0) {
            foreach ($lead->leadNeighborhood as $neighborhood) {
                $neighborhood_ids[] = $neighborhood->neighborhood_id;
            }
        }
        if ($lead->leadParking && !empty($lead->leadParking) > 0) {
            foreach ($lead->leadParking as $parking) {
                $parking_ids[] = $parking['parking'];
            }
        }

        $query = Post::where('post_status', 'publish')->where('post_type', @$lead->leadDetail->category)
                ->whereNotIn('ID', $propertyIds)
                ->select('ID');
        /*         * *OPEN HOUSE */
        /*if (isset($lead->leadDetail->category) && $lead->leadDetail->category == '$lead->leadDetail->category' && isset($lead->leadDetail->open_house) && !empty($lead->leadDetail->open_house)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where('meta_key', 'open_house')->where('meta_value', @$lead->leadDetail->open_house);
                    });
        }*/
        /*         * *SOLD UNDER CONTRACT */
        /*if (isset($lead->leadDetail->category) && $lead->leadDetail->category == '$lead->leadDetail->category' && isset($lead->leadDetail->sold_under_contract) && !empty($lead->leadDetail->sold_under_contract)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where('meta_key', 'sold_under_contract')->where('meta_value', @$lead->leadDetail->sold_under_contract);
                    });
        }*/
        /*         * *BATHROOM */
        if ($bathroom_ids && !empty($bathroom_ids)) {
            $query->whereHas('meta', function($q) use($bathroom_ids) {
                        $q->where('meta_key', 'bathrooms')->whereIn('meta_value', @$bathroom_ids);
                    });
        }
        /*         * *BEDROOM */
        if ($bedroom_ids && !empty($bedroom_ids)) {
            $query->whereHas('meta', function($q) use($bedroom_ids) {
                        $q->where('meta_key', 'bedrooms')->whereIn('meta_value', @$bedroom_ids);
                    });
        }
        /*         * *NEIGHBORHOOD */
        /* if($neighborhood_ids && !empty($neighborhood_ids)){
          $query->whereHas('meta', function($q) use($neighborhood_ids) {
          $q->where('meta_key','bedrooms')->whereIn('meta_value', @$neighborhood_ids);
          });
          } */
        /*         * * BATHROOM FILTER */
        if (isset($request->bathroom) && !empty($request->bathroom)) {
            $bathVal = Bathroom::where('id', '=', @$request->bathroom)->select('bathroom')->first();
            $query->whereHas('meta', function($q) use($bathVal) {
                        $q->where('meta_key', 'bathrooms')->where('meta_value', @$bathVal->bathroom);
                    });
        }
        /*         * * BEDROOM FILTER */
        if (isset($request->bedroom) && !empty($request->bedroom)) {
            $query->whereHas('meta', function($q) use($request) {
                        $q->where('meta_key', 'bedrooms')->where('meta_value', @$request->bedroom);
                    });
        }
        /*         * * NEIGHBORHOOD FILTER */
        if (isset($request->neighborhood) && !empty($request->neighborhood)) {
            $query->whereHas('taxonomies', function($q) use ($request) {
                        $q->where('taxonomy', 'neighbourhood');
                        $q->whereHas('term', function($qr) use ($request) {
                                    $qr->whereIn('term_id', explode(',', $request->neighborhood));
                                });
                    });
        }
        /*         * *BUDGET */
        if (!empty($lead->leadDetail->min_budget) && !empty($lead->leadDetail->max_budget)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where('meta_key', 'listing_price')->whereBetween('meta_value', [@$lead->leadDetail->min_budget, @$lead->leadDetail->max_budget]);
                    });
        } else {
            if (!empty($lead->leadDetail->min_budget)) {
                $query->whereHas('meta', function($q) use($lead) {
                            $q->where('meta_key', 'listing_price')->where('meta_value', '>=', $lead->leadDetail->min_budget);
                        });
            } else if (!empty($lead->leadDetail->max_budget)) {
                $query->whereHas('meta', function($q) use($lead) {
                            $q->where('meta_key', 'listing_price')->where('meta_value', '>=', $lead->leadDetail->max_budget);
                        });
            }
        }
        //    * *PARKING 
        /*if (!empty($lead->lead_parking)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'parking',
                            'meta_value' => @$lead->leadDetail->parking
                        ]);
                    });
        }*/
        if (!empty($parking_ids)) {
            $query->whereHas('taxonomies', function($q) use($lead) {
                        $q->where(['taxonomy' => 'parking',
                        ]);
                    });
            $query->whereHas('taxonomies.term', function($q) use($parking_ids) {
                    $q->orWhereIn('slug' ,$parking_ids,
                   );
                });
        }
        /*         * *LAUNDRY */
        /*if (!empty($lead->leadDetail->laundry)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'laundry',
                            'meta_value' => @$lead->leadDetail->laundry
                        ]);
                    });
        }*/
        /*         * *PETS 
        if (!empty($lead->leadDetail->pet)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'pets',
                            'meta_value' => @$lead->leadDetail->pet
                        ]);
                    });
        }
        /*         * *CITY */
        /*if (!empty($lead->leadDetail->city)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'city',
                            'meta_value' => @$lead->leadDetail->city
                        ]);
                    });
        }*/
        /*         * *STATE */
        /*if (!empty($lead->leadDetail->state_id)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'state',
                            'meta_value' => @$lead->leadDetail->state_id
                        ]);
                    });
        }*/
        /*         * *ZIPCODE */
        if (!empty($lead->leadDetail->zipcode)) {
            $query->whereHas('meta', function($q) use($lead) {
                        $q->where([
                            'meta_key' => 'zip_code',
                            'meta_value' => @$lead->leadDetail->zipcode
                        ]);
                    });
        }

        $posts = $query->pluck('ID')->toArray();
        $posts = array_values($posts);

        return $posts;
    }

    public function getMatchedProperties(Request $request) {

        $lead = Lead::where('id', '=', $request->id)->with('leadDetail', 'leadBedrooms', 'leadBathrooms','leadBathrooms.bathroomData','leadNeighborhood','leadParking')->first();
        //dd($lead);
        // echo "<pre>"; print_r($lead->toArray()); exit;
        $matchedPropertyIdsArr = $this->getMatchedPropertiesIds($lead, $request);

        $query = Post::where('post_status', 'publish')->where('post_type', @$lead->leadDetail->category)
                        ->whereIn('ID', $matchedPropertyIdsArr)
                        ->select('ID', 'post_title', 'post_type', 'post_author', 'post_name', 'post_modified')->orderBy('post_modified', 'Desc');

        $posts = $query->with([
            'meta:meta_id,post_id,meta_key,meta_value',
            'author:ID,user_login'
        ]);
        //print_r($posts->get()->toArray());
        return datatables()->of($posts)->addColumn('action', function ($posts)use ($request) {
                                    $actions = "<a title=\"View Lead\" href=\"" . $this->siteUrl . '/' . $posts->post_type . '/' . $posts->post_name . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                                    $isBookmarked = LeadAction::where(['user_id' => Auth::user()->ID, 'lead_id' => $request->id, 'property_id' => $posts->ID, 'action' => 1])->count();
                                    if ($isBookmarked <= 0) {
                                        $actions .= "<a title=\"Remove From Matched\" onclick=\"return confirm('Are you sure want to delete the Lead?')\" href=\"" . url('delete-matched-property?lead-id=' . $request->input('id') . '&property-id=' . $posts->ID . '&action=2') . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"glyphicon glyphicon-trash\"></i> Delete</a>&nbsp;";
                                        $actions .= "<a title=\"Bookmark\" onclick=\"return confirm('Are you sure want to make bookmark?')\" href=\"" . url('delete-matched-property?lead-id=' . $request->input('id') . '&property-id=' . $posts->ID . '&action=1') . "\" class=\"btn btn-xs btn-info btn-flat info-btn\"><i class=\"fa fa-bookmark\"></i> Bookmark</a>";
                                    } else {
                                        $actions .= "<a title=\"Remove From Matched\" onclick=\"return confirm('Are you sure want to delete the Lead?')\" href=\"" . url('delete-matched-property?lead-id=' . $request->input('id') . '&property-id=' . $posts->ID . '&action=3') . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"glyphicon glyphicon-trash\"></i> Delete</a>&nbsp;";
                                        $actions .= "<a title=\"Bookmarked\"  href=\"javascript:void(0);\" class=\"btn btn-xs btn-warning btn-flat info-btn\"><i class=\"fa fa-check\"></i> Bookmarked</a>";
                                    }
                                    return $actions;
                                })->addColumn('price', function($rows) {
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'listing_price') {
                                            return $row->value;
                                        }
                                    }
                                })->addColumn('info', function ($rows) {
                                    $acts = '';
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'bedrooms' && $row->value) {
                                            $acts .= "<strong>Bedroom</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'bathrooms' && $row->value) {
                                            $acts .= "<strong>Bathroom</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'parking' && $row->value) {
                                            $acts .= "<strong>Parking</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'laundry' && $row->value) {
                                            //$acts .= "<strong>Laundry</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'pets' && $row->value) {
                                            $acts .= "<strong>Pets</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    if ($rows->terms && !empty($rows->terms['neighbourhood'])) {
                                        $acts .= "<strong>Neighbourhood</strong>: " . implode(',', $rows->terms['neighbourhood']) . "<br >";
                                    }

                                    return $acts;
                                })
                        ->rawColumns(['post_title', 'action', 'info'])
                        ->make(true);
    }

    public function propertyDetail(Request $request) {
        $property = Post::with('author')->find($request->input('id'));
        return view('leads.propertyDetail', compact('property'));
    }

    public function deleteMatchedProperty(Request $request) {
        try {

            LeadAction::updateOrCreate(
                    ['user_id' => Auth::user()->ID, 'lead_id' => $request->input('lead-id'), 'property_id' => $request->input('property-id')], ['action' => $request->input('action')]
            );

            /* $leadAction = new LeadAction;
              $leadAction->user_id = Auth::user()->ID;
              $leadAction->lead_id = $request->input('lead-id');
              $leadAction->property_id = $request->input('property-id');
              $leadAction->action = $request->input('action');
              $leadAction->save(); */
            $status = "Deleted";
            if ($request->input('action') == 1)
                $status = "Bookmarked";
            return redirect()->back()->with('success', 'Property ' . $status . ' successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function publishedPropertiesIds() {
        $propertyPlublishedArr = Post::where('post_status', 'publish')->pluck('ID')->toArray();
        $propertyPlublishedArr = array_values($propertyPlublishedArr);

        return !empty($propertyPlublishedArr) ? $propertyPlublishedArr : [0 => 0];
    }

    public function bookmarkedProperties(Request $request) {



        return view('leads.bookmarkedProperties');
    }

    public function getBookmarkedProperties(Request $request) {
        $propertyPlublishedArr = $this->publishedPropertiesIds();

        if (Auth::user()->role == 'member') {

            $posts = LeadAction::where('user_id', Auth::user()->ID);
            $leadIds = Lead::where(['user_id' => Auth::user()->ID, 'is_deleted' => 0])->pluck('id');

            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid)->whereIn('lead_id', $leadIds);
            } else {
                $posts->whereIn('lead_id', $leadIds);
            }
        } else {
            $posts = LeadAction::where('user_id', Auth::user()->ID);
            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid);
            }
        }

        $posts->where(function($query) {
                    $query->where('action', 1);
                    $query->orWhere('action', 3);
                })->whereIn('property_id', $propertyPlublishedArr);
        /*
          if($request->bedroom){
          $posts->whereHas('post_detail', function($q) use ($request) {
          $q->whereHas('meta', function($qs) use($request) {
          $qs->where('meta_key','bedrooms')->where('meta_value', @$request->bedroom);
          });
          });
          }
         */
        $posts->with(['client_info' => function($q) {
                return $q->select('id', 'first_name', 'last_name');
            }, 'post_detail' => function($q)use ($request) {
                return $q->select('ID', 'post_title', 'post_type', 'post_author', 'post_name')
                                ->with([
                                    'meta:meta_id,post_id,meta_key,meta_value',
                                    'author:ID,display_name'
                ]);
            }]);

        return datatables()->of($posts)->addColumn('action', function ($posts)use ($request) {
                                    $actions = "<a title=\"View Property\"  target=\"_blank\" href=\"" . $this->siteUrl . '/' . @$posts->post_detail->post_type . '/' . @$posts->post_detail->post_name . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                                    $actions .= "<a title=\"Bookmarked\"  href=\"" . url('remove-bookmark?id=' . $posts->id) . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"fa fa-close\"></i> Remove Bookmark</a>";
                                    return $actions;
                                })->addColumn('ID', function($row) {
                                    return $row->post_detail->ID;
                                })->addColumn('price', function($rows) {
                                    foreach ($rows->post_detail->meta as $row) {
                                        if ($row->meta_key == 'listing_price') {
                                            return $row->value;
                                        }
                                    }
                                })->addColumn('client_name', function($row) {
                                    return ucwords(strtolower($row->client_info->first_name . ' ' . $row->client_info->last_name));
                                })->addColumn('info', function ($rows) {

                                    $acts = '';
                                    if ($rows->post_detail && $rows->post_detail->meta) {
                                        foreach ($rows->post_detail->meta as $row) {
                                            if ($row->meta_key == 'bedrooms') {
                                                $acts .= "<strong>Bedroom</strong>: " . $row->value . "<br >";
                                            }
                                        }
                                        foreach ($rows->post_detail->meta as $row) {
                                            if ($row->meta_key == 'bathrooms') {
                                                $acts .= "<strong>Bathroom</strong>: " . $row->value . "<br >";
                                            }
                                        }
                                        foreach ($rows->post_detail->meta as $row) {
                                            if ($row->meta_key == 'parking') {
                                                $acts .= "<strong>Parking</strong>: " . $row->value . "<br >";
                                            }
                                        }
                                        foreach ($rows->post_detail->meta as $row) {
                                            if ($row->meta_key == 'laundry') {
                                                //$acts .= "<strong>Laundry</strong>: " . $row->value . "<br >";
                                            }
                                        }
                                        foreach ($rows->post_detail->meta as $row) {
                                            if ($row->meta_key == 'pets') {
                                                $acts .= "<strong>Pets</strong>: " . $row->value . "<br >";
                                            }
                                        }
                                        if ($rows->post_detail->terms && !empty($rows->post_detail->terms['neighbourhood'])) {
                                            $acts .= "<strong>Neighbourhood</strong>: " . implode(',', $rows->post_detail->terms['neighbourhood']) . "<br >";
                                        }
                                    }


                                    return $acts;
                                })
                        ->rawColumns(['post_title', 'action', 'info'])
                        ->make(true);
    }

    public function xgetBookmarkedProperties(Request $request) {
        if (Auth::user()->role == 'member') {

            $posts = LeadAction::where('user_id', Auth::user()->ID);
            $leadIds = Lead::where(['user_id' => Auth::user()->ID, 'is_deleted' => 0])->pluck('id');

            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid)->whereIn('lead_id', $leadIds);
            } else {
                $posts->whereIn('lead_id', $leadIds);
            }
        } else {
            $posts = LeadAction::where('user_id', Auth::user()->ID);
            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid);
            }
        }
        $posts = $posts->where(function($query) {
                            $query->where('action', 1);
                            $query->orWhere('action', 3);
                        })->pluck('property_id');
        dd($posts);
    }

    public function deletedProperties(Request $request) {
        return view('leads.deletedProperties');
    }

    public function getDeletedProperties(Request $request) {
        $propertyPlublishedArr = $this->publishedPropertiesIds();

        if (Auth::user()->role == 'member') {
            $leadIds = Lead::where(['user_id' => Auth::user()->ID, 'is_deleted' => 0])->pluck('id');
            $posts = LeadAction::whereIn('lead_id', $leadIds);
            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid);
            }
        } else {
            $posts = LeadAction::where('user_id', Auth::user()->ID);
            if ($request->leadid && !empty($request->leadid)) {
                $posts->where('lead_id', $request->leadid);
            }
        }
        $posts->where(function($query) {
                            $query->where('action', 2);
                            $query->orWhere('action', 3);
                        })
                ->with([
                    'post_detail' => function($q) {
                        return $q
                                ->select('ID', 'post_title', 'post_type', 'post_author')
                                ->with([
                                    'meta:meta_id,post_id,meta_key,meta_value',
                                    'author:ID,display_name'
                        ]);
                    }
                ])->whereIn('property_id', $propertyPlublishedArr);

        return datatables()->of($posts)->addColumn('action', function ($posts)use ($request) {

                                    $actions = "<a title=\"View Lead\"  target=\"_blank\" href=\"" . url('property-detail?id=' . $posts->property_id) . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                                    $actions .= "<a title=\"Restore\"  href=\"" . url('restore?id=' . $posts->id) . "\" class=\"btn btn-xs btn-primary btn-flat info-btn\"><i class=\"fa fa-undo\"></i> Restore</a>";
                                    return $actions;
                                })->addColumn('ID', function($row) {
                                    return $row->post_detail->ID;
                                })->addColumn('price', function($rows) {
                                    foreach ($rows->post_detail->meta as $row) {
                                        if ($row->meta_key == 'listing_price') {
                                            return $row->value;
                                        }
                                    }
                                })->addColumn('address', function($rows) {
                                    foreach ($rows->post_detail->meta as $row) {
                                        if ($row->meta_key == 'address') {
                                            return $row->value;
                                        }
                                    }
                                })
                        ->rawColumns(['post_detail.post_title', '{{ $post_title }}'], ['action', '{{ $actions }}'])
                        ->make(true);
    }

    public function removeBookmark(Request $request) {
        try {
            if ($request->input('id') && !empty($request->input('id')) && LeadAction::where('id', '=', $request->input('id'))->exists()) {
                LeadAction::where('id', $request->input('id'))->delete();
                return redirect()->back()->with('success', 'Property remove from bookmark list successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function restore(Request $request) {
        try {
            if ($request->input('id') && !empty($request->input('id')) && LeadAction::where('id', '=', $request->input('id'))->exists()) {
                $getDetail = LeadAction::where('id', $request->input('id'))->first();
                if ($getDetail->action == 2) {
                    LeadAction::where('id', $request->input('id'))->delete();
                } else if ($getDetail->action == 3) {
                    LeadAction::where('id', $request->input('id'))->update(['action' => 1]);
                }
                return redirect()->back()->with('success', 'Property restored successfully!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function notificaitonView(Request $request) {
        DB::table('property_notifications')
                ->where('user_id', Auth::user()->ID)
                ->update(['is_read' => 1]);


        return view('leads.notifications');
    }

    public function getNotifications(Request $request) {
        $propertyPlublishedArr = $this->publishedPropertiesIds();
        $postIds = DB::table('property_notifications')->where('user_id', Auth::user()->ID)->pluck('property_id')->toArray();

        $propNotificationIds = array_intersect($propertyPlublishedArr, $postIds);

        $posts = Post::whereIn('ID', @$propNotificationIds)
                ->select('ID', 'post_title', 'post_type', 'post_author', 'post_name')
                ->with([
            'meta:meta_id,post_id,meta_key,meta_value',
            'author:ID,user_login'
        ]);
        return datatables()->of($posts)->addColumn('action', function ($posts)use ($request) {
                                    //print_r($posts->toArray());die;
                                    $actions = "<a title=\"View Property\"  target=\"_blank\"  href=\"" . $this->siteUrl . '/' . @$posts->post_type . '/' . @$posts->post_name . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                                    $actions .= "<a title=\"Matched Leads\" href=\"" . url('matched-leads?property-id=' . $posts->ID) . "\" class=\"btn btn-xs btn-warning btn-flat info-btn\"><i class=\" fa fa-users\"></i> Matched Leads</a>&nbsp;";
                                    $actions .= "<a title=\"Delete Lead\" onclick=\"return confirm('Are you sure want to delete the Lead?')\" href=\"" . url('delete-notification?property_id=' . $posts->ID) . "\" class=\"btn btn-xs btn-danger btn-flat info-btn\"><i class=\"glyphicon glyphicon-trash\"></i> Delete</a>&nbsp;";
                                    $actions .= "<a title=\"Send Notifications\"  target=\"_blank\" href=\"" . url('send-notifications?property-id=' . $posts->ID) . "\" class=\"btn btn-xs btn-primary btn-flat info-btn\"><i class=\"fa fa-bell-o\"></i> Send Notifications</a>&nbsp;";

                                    return $actions;
                                })->addColumn('price', function($rows) {
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'listing_price') {
                                            return $row->value;
                                        }
                                    }
                                })->addColumn('info', function ($rows) {
                                    $acts = '';
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'bedrooms') {
                                            $acts .= "<strong>Bedroom</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'bathrooms') {
                                            $acts .= "<strong>Bathroom</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'listing_price') {
                                            $acts .= "<strong>Budget</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'parking') {
                                            $acts .= "<strong>Parking</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'laundry') {
                                            //$acts .= "<strong>Laundry</strong>: " . $row->value . "<br >";
                                        }
                                    }
                                    foreach ($rows->meta as $row) {
                                        if ($row->meta_key == 'pets') {
                                            $acts .= "<strong>Pets</strong>: " . $row->value . "<br >";
                                        }
                                    }

                                    return $acts;
                                })
                        ->rawColumns(['post_title', 'action', 'info'])
                        ->make(true);
    }

    public function matchedLeadsView(Request $request) {
        if (!Post::where('ID', '=', $request->input('property-id'))->exists()) {
            return redirect()->back()->with('error', 'Invalid request!');
        }
        $property = Post::find($request->input('property-id'));
        return view('leads.matchedLeads', compact('property'));
    }

    public function getMatchedLeads(Request $request) {
        $property = Post::with('author', 'taxonomies')->find($request->propertyId);
        $query = Lead::where(['is_deleted' => 0, 'user_id' => Auth::user()->ID])->whereHas('leadDetail', function($q) use($property) {
                    $q->where('category', $property->post_type);
                });

        if ($property->meta) {
            foreach ($property->meta as $mtData) {
                /*                 * *BATHROOM */
                if ($mtData->meta_key == 'bathrooms' && $mtData->meta_value) {
                    $query->whereHas('leadBathrooms', function($q) use($mtData) {
                                $q->whereHas('bathroomData', function($q)use($mtData) {
                                            $q->where('bathroom', $mtData->meta_value);
                                        });
                            });
                }
                /*                 * *BEDROOM */
                if ($mtData->meta_key == 'bedrooms' && $mtData->meta_value) {
                    $query->whereHas('leadBedrooms', function($q) use($mtData) {
                                $q->whereHas('bedroomData', function($q)use($mtData) {
                                            $q->where('bedroom', $mtData->meta_value);
                                        });
                            });
                }
                /*                 * *BUDGET */
                if ($mtData->meta_key == 'listing_price' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('min_budget', '<=', $mtData->meta_value)->where('max_budget', '>=', $mtData->meta_value);
                            });
                }
                /*                 * *PARKING */
                if ($mtData->meta_key == 'parking' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('parking', $mtData->meta_value);
                            });
                }
                /*                 * *LAUNDRY */
                /*if ($mtData->meta_key == 'laundry' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('laundry', $mtData->meta_value);
                            });
                }*/
                /*                 * *PETS */
                if ($mtData->meta_key == 'pets' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('pet', $mtData->meta_value);
                            });
                }
                /*                 * *CITY */
                /*if ($mtData->meta_key == 'city' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where(['city' => $mtData->meta_value]);
                            });
                }*/
                /*                 * *STATE */
                /*if ($mtData->meta_key == 'state' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('state_id', $mtData->meta_value);
                            });
                }*/
                /*                 * *ZIPCODE */
                if ($mtData->meta_key == 'zip_code' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('zipcode', $mtData->meta_value);
                            });
                }
            }
        }
        /*         * *NEIGHBORHOOD */
        if ($property->taxonomies) {
            $taxonomyArr = [];
            foreach ($property->taxonomies as $taxnomies) {
                if ($taxnomies->taxonomy == "neighbourhood") {
                    $taxonomyArr[] = $taxnomies->term_taxonomy_id;
                }
            }
            if (!empty($taxonomyArr)) {
                $query->whereHas('leadNeighborhood', function($q) use($taxonomyArr) {
                            $q->whereIn('neighborhood_id', $taxonomyArr);
                        });
            }
        }
        $leads = $query->with('leadDetail')->orderBy('created_at', 'DESC');

        return datatables()->of($leads)->addColumn('action', function ($leads)use ($request) {
                                    $actions = "<a title=\"View Lead\" href=\"" . route('leads.show', ['id' => $leads->id]) . "\" class=\"btn btn-xs btn-success btn-flat info-btn\"><i class=\" fa fa-eye\"></i> View</a>&nbsp;";
                                    return $actions;
                                })->addColumn('full_name', function($row) {
                                    return ucwords(strtolower($row->first_name . ' ' . $row->last_name));
                                })
                        ->filterColumn('full_name', function ($query, $keyword) {
                                    $keywords = trim($keyword);
                                    $query->whereRaw("CONCAT(first_name, last_name) like ?", ["%{$keywords}%"]);
                                })
                        ->rawColumns(['action'])
                        ->make(true);
    }

    public function deleteNotifications(Request $request) {
        if ($request->input('property_id') && !empty($request->input('property_id'))) {
            if (DB::table('property_notifications')->where(['user_id' => Auth::user()->ID, 'property_id' => $request->input('property_id')])->delete()) {
                return redirect('notification-list')->with('success', 'Notification deleted successfully!');
            } else {
                return redirect()->back()->with('error', 'Something went wrong. Please try again!');
            }
        } else {
            return redirect()->back()->with('error', 'Something went wrong. Please try again!');
        }
    }

    public function sendNotification(Request $request) {
        $property = Post::with('author', 'taxonomies')->find($request->input('property-id'));
        $query = Lead::where(['is_deleted' => 0, 'user_id' => Auth::user()->ID])->whereHas('leadDetail', function($q) use($property) {
                    $q->where('category', $property->post_type);
                });

        if ($property->meta) {
            foreach ($property->meta as $mtData) {
                /*                 * *BATHROOM */
                if ($mtData->meta_key == 'bathrooms' && $mtData->meta_value) {
                    $query->whereHas('leadBathrooms', function($q) use($mtData) {
                                $q->whereHas('bathroomData', function($q)use($mtData) {
                                            $q->where('bathroom', $mtData->meta_value);
                                        });
                            });
                }
                /*                 * *BEDROOM */
                if ($mtData->meta_key == 'bedrooms' && $mtData->meta_value) {
                    $query->whereHas('leadBedrooms', function($q) use($mtData) {
                                $q->whereHas('bedroomData', function($q)use($mtData) {
                                            $q->where('bedroom', $mtData->meta_value);
                                        });
                            });
                }
                /*                 * *BUDGET */
                if ($mtData->meta_key == 'listing_price' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('min_budget', '<=', $mtData->meta_value)->where('max_budget', '>=', $mtData->meta_value);
                            });
                }
                /*                 * *PARKING */
                if ($mtData->meta_key == 'parking' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('parking', $mtData->meta_value);
                            });
                }
                /*                 * *LAUNDRY */
                /*if ($mtData->meta_key == 'laundry' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('laundry', $mtData->meta_value);
                            });
                }*/
                /*                 * *PETS */
                if ($mtData->meta_key == 'pets' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('pet', $mtData->meta_value);
                            });
                }
                /*                 * *CITY */
                /*if ($mtData->meta_key == 'city' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where(['city' => $mtData->meta_value]);
                            });
                }*/
                /*                 * *STATE */
                /*if ($mtData->meta_key == 'state' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('state_id', $mtData->meta_value);
                            });
                }*/
                /*                 * *ZIPCODE */
                if ($mtData->meta_key == 'zip_code' && $mtData->meta_value) {
                    $query->whereHas('leadDetail', function($q) use($mtData) {
                                $q->where('zipcode', $mtData->meta_value);
                            });
                }
            }
        }
        /*         * *NEIGHBORHOOD */
        if ($property->taxonomies) {
            $taxonomyArr = [];
            foreach ($property->taxonomies as $taxnomies) {
                if ($taxnomies->taxonomy == "neighbourhood") {
                    $taxonomyArr[] = $taxnomies->term_taxonomy_id;
                }
            }
            if (!empty($taxonomyArr)) {
                $query->whereHas('leadNeighborhood', function($q) use($taxonomyArr) {
                            $q->whereIn('neighborhood_id', $taxonomyArr);
                        });
            }
        }

        $users = $query->select('first_name', 'last_name', 'email')->get();
        $propertyDetailLink = $this->siteUrl . '/' . $property->post_type . '/' . $property->post_name;

        foreach ($users as $user) {
        
            $result=Mail::send('send', ['user' => $user,"propertyName"=>$property->post_name, 'propertylink' => $propertyDetailLink], function($m) use ($user) {
                        $m->to($user->email)->subject('New Property');
                    });
                    //dd($result);die;
        }
        return redirect('notification-list')->with('success', 'Notification sent successfully!');
    }

}