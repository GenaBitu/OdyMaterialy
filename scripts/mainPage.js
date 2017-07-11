function getMainPage(noHistory)
{
	lessonListEvent.addCallback(function()
		{
			showMainPage(noHistory);
		});
}

function showMainPage(noHistory)
{
	var html = "<h1>OdyMateriály</h1>";
	html += renderLessonList();
	document.getElementById("content").innerHTML = html;

	nodes = document.getElementById("content").getElementsByTagName("h3");
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].firstChild.onclick = itemOnClick;
	}

	document.getElementsByTagName("main")[0].scrollTop = 0;
	if(!noHistory)
	{
		history.pushState({}, "title", "/");
	}
	document.getElementById("offlineSwitch").style.display = "none";
}

function renderLessonList()
{
	var html = "";
	for(var i = 0; i < FIELDS.length; i++)
	{
		html += "<h2 class=\"mainPage\">" + FIELDS[i].name + "</h2>";
		for(var j = 0; j < FIELDS[i].lessons.length; j++)
		{
			var name = FIELDS[i].lessons[j].name;
			html += "<h3 class=\"mainPage\"><a title=\"" + name + "\" href=\"/error/enableJS.html\" data-id=\"" + FIELDS[i].lessons[j].id + "\">" + name + "</a></h3>";
			if(FIELDS[i].lessons[j].competences.length > 0)
			{
				var competences = [];
				for(var k = 0; k < COMPETENCES.length; k++)
				{
					for(var l = 0; l < FIELDS[i].lessons[j].competences.length; l++)
					{
						if(FIELDS[i].lessons[j].competences[l].id === COMPETENCES[k].id)
						{
							competences.push(COMPETENCES[k]);
							break;
						}
					}
				}
				html += "<span class=\"mainPage\">Kompetence: " + FIELDS[i].lessons[j].competences[0].number;
				for(var m = 1; m < competences.length; m++)
				{
					html += ", " + competences[m].number;
				}
				html += "</span>";
			}
		}
	}
	return html;
}
