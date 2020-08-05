// see license.txt for licensing
var kfm_kaejax_replaces={'([89A-F][A-Z0-9])':'%u00$1','22':'"','2C':',','3A':':','5B':'[','5D':']','7B':'{','7D':'}'};
if(window.ie){
	window.kfm_kaejax_replaces_regexps=[];
	window.kfm_kaejax_replaces_replacements=[];
	for(var i in kfm_kaejax_replaces){
		kfm_kaejax_replaces_regexps.push(eval('/%'+i+'/g'));
		kfm_kaejax_replaces_replacements.push(kfm_kaejax_replaces[i]);
	}
}
else{
	for(var a in kfm_kaejax_replaces){
		kfm_kaejax_replaces[kfm_kaejax_replaces[a]]=eval('/%'+a+'/g');
		delete kfm_kaejax_replaces[a];
	}
}
var kfm_sanitise_ajax=window.ie?
	function(d){
		for(var a in window.kfm_kaejax_replaces_regexps)d=d.replace(kfm_kaejax_replaces_regexps[a],kfm_kaejax_replaces_replacements[a]);
		return d;
	}:
	function(d){
		var r=kfm_kaejax_replaces;
		for(var a in r)d=d.replace(r[a],a);
		return d;
	};
