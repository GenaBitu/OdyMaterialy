var navOpen = true;

function navSetup()
{
	window.addEventListener("resize", reflow)
	document.getElementById("navCloseButton").onclick = switchNav;
	document.getElementById("overlay").onclick = switchNav;
	document.getElementById("lessonOverview").onclick = function()
		{
			getMainPage();
			return false;
		}
	reflow();
}

function switchNav()
{
	navOpen = !navOpen;
	reflow();
}

function reflow()
{
	main = document.getElementsByTagName("main")[0].style;
	navBar = document.getElementsByTagName("nav")[0].style;
	overlay = document.getElementById("overlay").style;
	if(navOpen)
	{
		navBar.marginLeft = "0px"
		if(screen.width > 700)
		{
			main.marginLeft = "300px"
			overlay.display = "none";
		}
		else
		{
			main.marginLeft = "0px"
			overlay.display = "inline";
		}
	}
	else
	{
		navBar.marginLeft = "-300px"
		main.marginLeft = "0px"
		overlay.display = "none";
	}
}

