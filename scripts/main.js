var CACHE = "odymaterialy-v1";

function main()
{
	navSetup();
	topUIsetup();
	historySetup();
	authSetup();
	listLessonsSetup();
	getLessonSetup();
	if("serviceWorker" in navigator)
	{
		navigator.serviceWorker.register("/serviceworker.js");
	}
}

window.onload = main;
