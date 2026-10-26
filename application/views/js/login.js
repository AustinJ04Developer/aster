function loadimage()
{ 
//var v1 = document.getElementById("username").val
	if(document.getElementById("username").value == "")
	{
		return false;
	}
	else
	{
	document.getElementById("loading").style.display = "block";
	}
}

function loadimageforgetpassword()
{ 
//var v1 = document.getElementById("username").val
	if(document.getElementById("emailid").value == "")
	{
		return false;
	}
	else
	{
	document.getElementById("loading").style.display = "block";
	}
}
