self.addEventListener('message', function(e) {
	//self.postMessage(e.data);
	//console.log(e.data);
	if (e.data.op=='fetch'){
		fetch(e.data.u,{mode: 'no-cors'}); 
	}
}, false); 