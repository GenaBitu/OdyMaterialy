var lessonListEvent = new AfterLoadEvent(3);
var mainPageTab = "lessons";
var FIELDS = [];
var COMPETENCES = [];
var LOGINSTATE = [];

function mainPageSetup()
{
	getMainPage();
	lessonListSetup();
}

function lessonListSetup()
{
	request("/API/v0.9/list_lessons", "", function(response)
		{
			FIELDS = JSON.parse(response);
			lessonListEvent.trigger();
		});
	request("/API/v0.9/list_competences", "", function(response)
		{
			COMPETENCES = JSON.parse(response);
			lessonListEvent.trigger();
		});
	request("/API/v0.9/get_login_state", "", function(response)
		{
			LOGINSTATE = JSON.parse(response);
			lessonListEvent.trigger();
		});
}

function getMainPage(noHistory)
{
	lessonListEvent.addCallback(function()
		{
			showMainPage(noHistory);
		});
}

function showMainPage(noHistory)
{
	var html = "<div id=\"sidePanel\"></div><div id=\"sidePanelOverlay\"></div>";
	html += "<div id=\"topBar\"><div id=\"userAccount\"><img id=\"userAvatar\" alt=\"Account avatar\" src=\"";
	if(LOGINSTATE.user_avatar)
	{
		html += "data:image/png;base64," + LOGINSTATE.user_avatar;
	}
	else
	{
		html += "/images/avatar.png";
	}
	html += "\"><div id=\"userName\">";
	html += LOGINSTATE.user_name;
	html += "</div><div id=\"logLink\"><a href=\"/auth/logout.php\">Odhlásit</a></div></div></div>";
	html += "<div id=\"mainPageContainer\"><div id=\"mainPage\"></div></div>";
	document.getElementsByTagName("main")[0].innerHTML = html;
	document.getElementsByTagName("main")[0].scrollTop = 0;

	if(mainPageTab == "competences")
	{
		showCompetenceManager();
	}
	else
	{
		showLessonManager();
	}

	if(!noHistory)
	{
		history.pushState({"lessonName": ""}, "title", "/admin/");
	}
}

function addOnClicks(id, onclick)
{
	var nodes = document.getElementsByTagName("main")[0].getElementsByClassName(id);
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].onclick = onclick;
	}
}
