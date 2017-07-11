function AfterLoadEvent(threshold)
{
	this.triggered = false;
	this.threshold = threshold;
	this.count = 0;
	this.callbacks = [];
	this.addCallback = function(callback)
		{
			if(this.triggered)
			{
				callback();
			}
			else
			{
				this.callbacks.push(callback);
			}
		};
	this.trigger = function()
		{
			this.count++;
			if(this.count >= this.threshold)
			{
				this.triggered = true;
				for(var i = 0; i < this.callbacks.length; i++)
				{
					this.callbacks[i]();
				}
			}
		};
}