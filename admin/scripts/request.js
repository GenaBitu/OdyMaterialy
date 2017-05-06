function request(url, query, callback)
{
	var xhr = new XMLHttpRequest();
	xhr.onreadystatechange = function()
		{
			if(this.readyState === 4 && this.status === 200)
			{
				callback(this.responseText);
			}
		}
	if(query !== undefined && query !== "")
	{
		url += "?" + query;
	}
	xhr.open("GET", url, true);
	xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xhr.send();
}
