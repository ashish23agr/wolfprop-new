<html>
<head> Hi <?php 

if($user && isset($user->first_name) && !empty($user->first_name)&& isset($user->last_name) && !empty($user->last_name))
	echo ucwords(strtolower(@$user->first_name.' '.@$user->last_name));
else if($user && isset($user->first_name) && !empty($user->first_name))
	echo ucwords(strtolower(@$user->first_name));
else if($user && isset($user->last_name) && !empty($user->last_name))
	echo ucwords(strtolower(@$user->last_name));
else
	echo "Greetings";?>,</head>
<body>
<p>We have new property related to your search criteria.</p>
<p><a href="<?php echo $propertylink;?>">Click here </a> to visit</p>
<p>Regards,<br></p>
<p>Wolf Properties Team</p>
</body>
</html>