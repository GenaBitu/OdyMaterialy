var FIELDS;
var COMPETENCES;
var LOGINSTATE;

function main()
{
	navigationSetup();
	headerSetup();
	historySetup();
	authenticationSetup();
	metadataSetup();
	lessonViewSetup();
	TOCSetup();
	if("serviceWorker" in navigator)
	{
		navigator.serviceWorker.register("/serviceworker.js");
	}
	WebFont.load({
		google: {
			families: ["Open Sans:400,400i,700,700i"]
		}
	});
}

window.onload = main;
