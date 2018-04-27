"use strict";

var metadataEvent = new AfterLoadEvent(3);

function metadataSetup()
{
	configEvent.addCallback(refreshMetadata);
}

function refreshMetadata()
{
	cacheThenNetworkRequest(CONFIG.apiuri + "/lesson", undefined, function(response, second)
		{
			window.FIELDS = response;
			if(second)
			{
				metadataEvent.retrigger();
			}
			else
			{
				metadataEvent.trigger();
			}
		});
	cacheThenNetworkRequest(CONFIG.apiuri + "/competence", undefined, function(response, second)
		{
			window.COMPETENCES = response;
			if(second)
			{
				metadataEvent.retrigger();
			}
			else
			{
				metadataEvent.trigger();
			}
		});
	var xhttp = new XMLHttpRequest();
	xhttp.onreadystatechange = function()
		{
			if(this.readyState === 4)
			{
				var response = {};
				if(this.responseText)
				{
					response = JSON.parse(this.responseText);
				}
				if(response.status === 200)
				{
					window.LOGINSTATE = response.response;
				}
				else
				{
					window.LOGINSTATE = undefined;
				}
				metadataEvent.trigger();
			}
		}
	xhttp.timeout = 5000;
	xhttp.open("GET", CONFIG.apiuri + "/account", true);
	xhttp.send();
}
