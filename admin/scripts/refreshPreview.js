var converter;
var worker;
var running = false;
var queue;

function refreshPreviewSetup()
{
	if(window.Worker)
	{
		worker = new Worker("scripts/previewWorker.js");
		worker.onmessage = function(message)
		{
			document.getElementById("preview-inner").innerHTML = message.data;
			if(queue)
			{
				worker.postMessage(queue);
				queue = undefined;
			}
			else
			{
				running = false;
			}
		}
	}
	else
	{
		converter = new showdown.Converter({extensions: ["notes"]});
		converter.setOption("noHeaderId", "true");
		converter.setOption("tables", "true");
	}
}

function refreshPreview(name, markdown)
{
	if(window.Worker)
	{
		if(running)
		{
			queue = markdown;
		}
		else
		{
			running = true;
			worker.postMessage(markdown);
		}
	}
	else
	{
		var html = "<h1>" + name + "</h1>";
		html += converter.makeHtml(markdown);
		document.getElementById("preview").innerHTML = html;
	}
}

