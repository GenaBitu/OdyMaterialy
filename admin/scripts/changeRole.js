var roleChanged = false;

function changeRoleOnClick(event)
{
	roleChanged = false;
	sidePanelOpen();
	var html = "";
	html += "<h3 class=\"sidePanelTitle\">" + event.target.dataset.name + "</h3><div class=\"button\" id=\"sidePanelCancel\"><i class=\"icon-cancel\"></i>Zrušit</div><div class=\"button\" id=\"changeRoleSave\" data-id=\"" + event.target.dataset.id + "\"><i class=\"icon-floppy\"></i>Uložit</div><form id=\"sidePanelForm\">";
	html += "<span class=\"roleText\">Role: </span><select class=\"formSelect\" id=\"roleSelect\">";
	html += "<option id=\"guest\" value=\"guest\">Host</option>";
	html += "<option id=\"user\" value=\"user\">Uživatel</option>";
	if(LOGINSTATE.role == "administrator" || LOGINSTATE.role == "superuser")
	{
		html += "<option id=\"editor\" value=\"editor\">Editor</option>";
	}
	if(LOGINSTATE.role == "superuser")
	{
		html += "<option id=\"administrator\" value=\"administrator\">Administrátor</option>";
		html += "<option id=\"superuser\" value=\"superuser\">Superuser</option>";
	}
	html += "</select>";
	html += "</form>";
	html += "<div class=\"roleHelp\"><i class=\"icon-info-circled\"></i><b>Host</b> - Kdokoliv, kdo se někdy přihlásil do OdyMateriálů pomocí skautISu. Nemá žádná oprávnění navíc oproti nepřihlášeným návštěvníkům.</div>";
	html += "<div class=\"roleHelp\"><i class=\"icon-info-circled\"></i><b>Uživatel</b> - Účastník kurzu, může zobrazit i lekce, které jsou nepřihlášeným a hostům skryté.</div>";
	if(LOGINSTATE.role == "administrator" || LOGINSTATE.role == "superuser")
	{
		html += "<div class=\"roleHelp\"><i class=\"icon-info-circled\"></i><b>Editor</b> - Instruktor, který má základní přístup k správě OdyMateriálů. Může přidávat lekce, měnit jejich obsah, kompetence a přesouvat je mezi oblastmi. Editor má přístup ke správě uživatelů, avšak může prohlížet a měnit pouze hosty a uživatele.</div>";
	}
	if(LOGINSTATE.role == "superuser")
	{
		html += "<div class=\"roleHelp\"><i class=\"icon-info-circled\"></i><b>Administrátor</b> - Instruktor, mající všechna práva editora. Navíc může i mazat lekce a přidávat, upravovat a mazat oblasti a kompetence. Administrátor může navíc přidělovat a odebírat práva editorů.</div>";
		html += "<div class=\"roleHelp\"><i class=\"icon-info-circled\"></i><b>Superuser</b> - Uživatel-polobůh.</div>";
	}
	document.getElementById("sidePanel").innerHTML = html;

	document.getElementById("roleSelect").options.namedItem(event.target.dataset.role).selected = 'selected';

	document.getElementById("sidePanelCancel").onclick = function()
		{
			history.back();
		};
	document.getElementById("changeRoleSave").onclick = changeRoleSave;
	document.getElementById("roleSelect").onchange = function()
		{
			roleChanged = true;
		};

	history.pushState({"sidePanel": "open"}, "title", "/admin/");
}

function changeRoleSave()
{
	if(roleChanged)
	{
		var query = "id=" + encodeURIComponent(document.getElementById("changeRoleSave").dataset.id);
		var sel = document.getElementById("roleSelect");
		query += "&role=" + encodeURIComponent(sel.options[sel.selectedIndex].value);
		sidePanelClose();
		retryAction("/API/v0.9/update_user_role", query);
	}
}
