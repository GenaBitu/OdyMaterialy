var lessonListEvent = new AfterLoadEvent(3);
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
	var html = "<div id=\"sidePanel\"></div><div id=\"sidePanelOverlay\"></div><div id=\"mainPageContainer\"><div id=\"mainPage\">";
	html += "<h1>OdyMateriály - administrace</h1>";
	if(LOGINSTATE.role == "administrator" || LOGINSTATE.role == "superuser")
	{
		html += "<div class=\"button\" id=\"addField\">Přidat oblast</div>";
	}
	html += "<div class=\"button\" id=\"addLesson\">Přidat lekci</div><br>";
	html += renderLessonList();
	html += "</div></div>";
	document.getElementsByTagName("main")[0].innerHTML = html;
	
	document.getElementById("addField").onclick = function()
		{
			addField();
		};
	document.getElementById("addLesson").onclick = function()
		{
			addLesson();
		};

	nodes = document.getElementsByTagName("main")[0].getElementsByTagName("h3");
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].firstChild.onclick = itemOnClick;
	}

	function addOnClicks(id, onclick)
	{
		var nodes = document.getElementsByTagName("main")[0].getElementsByClassName(id);
		for(var l = 0; l < nodes.length; l++)
		{
			nodes[l].onclick = onclick;
		}
	}
	addOnClicks("changeField", changeFieldOnClick);
	addOnClicks("deleteField", deleteFieldOnClick);
	addOnClicks("changeLesson", itemOnClick);
	addOnClicks("changeLessonField", changeLessonFieldOnClick);
	addOnClicks("changeLessonCompetences", changeLessonCompetencesOnClick);
	addOnClicks("deleteLesson", deleteLessonOnClick);

	document.getElementsByTagName("main")[0].scrollTop = 0;
	var stateObject = { lessonName: "" };
	if(!noHistory)
	{
		history.pushState(stateObject, "title", "/admin/");
	}
}

function itemOnClick(event)
{
	getLesson(event.target.dataset.id);
	return false;
}

function renderLessonList()
{
	var html = "";
	for(var i = 0; i < FIELDS.length; i++)
	{
		var secondLevel = "";
		if(FIELDS[i].name)
		{
			secondLevel = " secondLevel";
			html += "<h2 class=\"mainPage\">" + FIELDS[i].name + "</h2>";
			if(LOGINSTATE.role == "administrator" || LOGINSTATE.role == "superuser")
			{
				html += "<div class=\"button mainPage changeField\" data-id=\"" + FIELDS[i].id + "\">Upravit oblast</div>";
				html += "<div class=\"button mainPage deleteField\" data-id=\"" + FIELDS[i].id + "\">Smazat oblast</div>";
			}
		}
		for(var j = 0; j < FIELDS[i].lessons.length; j++)
		{
			html += "<h3 class=\"mainPage" + secondLevel + "\"><a title=\"" + FIELDS[i].lessons[j].name + "\" href=\"/error/enableJS.html\" data-id=\"" + FIELDS[i].lessons[j].id + "\">" + FIELDS[i].lessons[j].name + "</a></h3>";
			if(FIELDS[i].lessons[j].competences.length > 0)
			{
				var competences = [];
				for(var k = 0; k < COMPETENCES.length; k++)
				{
					if(FIELDS[i].lessons[j].competences.indexOf(COMPETENCES[k].id) >= 0)
					{
						competences.push(COMPETENCES[k]);
					}
				}
				html += "<span class=\"mainPage" + secondLevel + "\">Kompetence: " + competences[0].number;
				for(var m = 1; m < competences.length; m++)
				{
					html += ", " + competences[m].number;
				}
				html += "</span><br>";
			}
			html += "<div class=\"button mainPage" + secondLevel + " changeLesson\" data-id=\"" + FIELDS[i].lessons[j].id + "\">Upravit lekci</div>";
			html += "<div class=\"button mainPage changeLessonField\" data-id=\"" + FIELDS[i].lessons[j].id + "\">Změnit oblast</div>";
			html += "<div class=\"button mainPage changeLessonCompetences\" data-id=\"" + FIELDS[i].lessons[j].id + "\">Změnit kompetence</div>";
			if(LOGINSTATE.role == "administrator" || LOGINSTATE.role == "superuser")
			{
				html += "<div class=\"button mainPage deleteLesson\" data-id=\"" + FIELDS[i].lessons[j].id + "\">Smazat lekci</div>";
			}
		}
	}
	return html;
}
