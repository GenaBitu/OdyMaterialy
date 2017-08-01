function showUserManager()
{
	mainPageTab = "users";
	var nodes = document.getElementsByClassName("topBarTab");
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].className = "topBarTab";
	}
	document.getElementById("userManager").className += " activeTopBarTab";
	var html = "<h1>OdyMateriály - Uživatelé</h1><div id=\"userList\">";
	document.getElementById("mainPage").innerHTML = html;
	getUserList();
}

function getUserList(searchName, page, perPage)
{
	if(!searchName)
	{
		searchName = "";
	}
	if(!page)
	{
		page = 1;
	}
	if(!perPage)
	{
		perPage = 25;
	}
	var query = "name=" + searchName + "&page=" + page + "&per-page=" + perPage;
	request("/API/v0.9/list_users", query, function(response)
		{
			showUserList(JSON.parse(response), searchName, page, perPage);
		});
}

function showUserList(list, searchName, page, perPage)
{
	users = list.users;
	var html = "<form id=\"userSearchForm\"><input type=\"text\" class=\"formText\" id=\"userSearchBox\" placeholder=\"Jméno uživatele\"><div class=\"button\" id=\"userSearchButton\">Vyhledat</div>";
	if(searchName)
	{
		html += "<div class=\"button\" id=\"userSearchCancel\"><i class=\"icon-cancel\"></i>Zrušit</div>";
	}
	html += "</form>";
	html += "<table class=\"userTable\"><th>Jméno</th><th>Role</th><th>Akce</th></tr>";
	for(var i = 0; i < users.length; i++)
	{
		html += "<tr><td>" + users[i].name + "</td><td>";
		switch(users[i].role)
		{
			case "superuser":
				html += "Superuser";
				break;
			case "administrator":
				html += "Administrátor";
				break;
			case "editor":
				html += "Editor";
				break;
			case "user":
				html += "Uživatel";
				break;
			default:
				html += "Host";
				break;
		}
		html += "</td><td><div class=\"button changeRole\" data-id=\"" + users[i].id + "\" data-role=\"" + users[i].role + "\" data-name=\"" + users[i].name + "\">Změnit roli</div></td></tr>";
	}
	html += "</table>";
	if(list.count > perPage)
	{
		var maxPage = Math.ceil(list.count / perPage);

		function renderPage(page)
		{
			html += "<div class=\"paginationButton\" data-page=\"" + page + "\">" + page + "</div>";
		}

		html += "<div id=\"pagination\">";
		if(page > 3)
		{
			renderPage(1);
			html += " ... ";
		}
		if(page > 2)
		{
			renderPage(page - 2);
		}
		if(page > 1)
		{
			renderPage(page - 1);
		}
		html += "<div class=\"paginationButton active\">" + page + "</div>";
		if(page < maxPage)
		{
			renderPage(page + 1);
		}
		if(page < maxPage - 1)
		{
			renderPage(page + 2);
		}
		if(page < maxPage - 2)
		{
			html += " ... ";
			renderPage(maxPage);
		}
		html += "</div>";
	}
	document.getElementById("userList").innerHTML = html;

	document.getElementById("userSearchForm").onsubmit = function()
		{
			getUserList(document.getElementById("userSearchBox").value, 1, perPage);
			return false;
		}
	document.getElementById("userSearchButton").onclick = function()
		{
			getUserList(document.getElementById("userSearchBox").value, 1, perPage);
		};
	if(searchName)
		{
			document.getElementById("userSearchCancel").onclick = function()
				{
					getUserList("", 1, perPage);
				};
		}
	var nodes = document.getElementsByClassName("paginationButton");
	for(var l = 0; l < nodes.length; l++)
	{
		nodes[l].onclick = function(event)
			{
				getUserList(searchName, parseInt(event.target.dataset.page), perPage);
			};
	}

	addOnClicks("changeRole", changeRoleOnClick);
}