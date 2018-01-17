var MDimageSelectorOpen = false;

function MDprepareImageSelector(page, perPage)
{
	if(!page)
	{
		page = 1;
	}
	if(!perPage)
	{
		perPage = 15;
	}
	request("/API/v0.9/image", "GET", "", function(response)
		{
			if(response.status === 200)
			{
				MDrenderImageSelector(response.response, page, perPage);
			}
			else
			{
				dialog("Nastala neznámá chyba. Chybová hláška:<br>" + response.message, "OK");
			}
		});
	refreshLogin();
}

function MDrenderImageSelector(list, page, perPage)
{
	var html = "";
	var start = perPage * (page - 1);
	for(var i = start; i < Math.min(list.length, start + perPage); i++)
	{
		html += "<img src=\"/API/v0.9/image/" + list[i] + "?quality=thumbnail\" class=\"thumbnailImage\" data-id=\"" + list[i] + "\">";
	}
	if(list.length > perPage)
	{
		var maxPage = Math.ceil(list.length / perPage);
		html += "<div id=\"pagination\">";
		if(page > 3)
		{
			html += "<div class=\"paginationButton\" data-page=\"1\">1</div>";
			html += " ... ";
		}
		if(page > 2)
		{
			html += "<div class=\"paginationButton\" data-page=\"" + (page - 2) + "\">" + (page - 2) + "</div>";
		}
		if(page > 1)
		{
			html += "<div class=\"paginationButton\" data-page=\"" + (page - 1) + "\">" + (page - 1) + "</div>";
		}
		html += "<div class=\"paginationButton active\">" + page + "</div>";
		if(page < maxPage)
		{
			html += "<div class=\"paginationButton\" data-page=\"" + (page + 1) + "\">" + (page + 1) + "</div>";
		}
		if(page < maxPage - 1)
		{
			html += "<div class=\"paginationButton\" data-page=\"" + (page + 2) + "\">" + (page + 2) + "</div>";
		}
		if(page < maxPage - 2)
		{
			html += " ... ";
			html += "<div class=\"paginationButton\" data-page=\"" + maxPage + "\">" + maxPage + "</div>";
		}
		html += "</div>";
	}
	document.getElementById("imageWrapper").innerHTML = html;

	var	nodes = document.getElementById("imageWrapper").getElementsByTagName("img");
	for(var k = 0; k < nodes.length; k++)
	{
		nodes[k].onclick = MDinsertImage;
	}
	nodes = document.getElementsByClassName("paginationButton");
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].onclick = function(event)
			{
				MDprepareImageSelector(parseInt(event.target.dataset.page, 10), perPage);
			};
	}
}

function MDtoggleImageSelector()
{
	if(MDimageSelectorOpen)
	{
		document.getElementById("MDimageSelector").style.top = "-100%";
	}
	else
	{
		document.getElementById("MDimageSelector").style.top = "-91px";
	}
	MDimageSelectorOpen = !MDimageSelectorOpen;
	refreshLogin();
}

function MDinsertImage(event)
{
	var markdown = "![Text po najetí kurzorem](https://odymaterialy.skauting.cz/API/v0.9/image/" + event.target.dataset.id + ")"
	var doc = editor.codemirror.getDoc();
	doc.replaceRange(markdown, doc.getCursor());
	MDtoggleImageSelector();
	refreshLogin();
}
