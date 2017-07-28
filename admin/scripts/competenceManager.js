function showCompetenceManager()
{
	var html = "<h1>OdyMateriály - Správce kompetencí</h1>";
	html += "<div class=\"button mainPage\" id=\"lessonManager\">Správce lekcí</div>";
	html += renderCompetenceList()
	document.getElementById("mainPage").innerHTML = html;

	document.getElementById("lessonManager").onclick = function()
		{
			showLessonManager();
		};
}

function renderCompetenceList()
{
	var html = "";
	for(var i = 0; i < COMPETENCES.length; i++)
	{
		html += "<h3 class = \"mainPage\"><i>" + COMPETENCES[i].number + ":</i> " + COMPETENCES[i].name + "</h3><span class=\"mainPage\">" + COMPETENCES[i].description + "</span>";
	}
	return html;
}
