function File(id,data){
};
File.prototype.getText=function(varname){
	var el=document.createElement('span');
	el.className=varname+' file_'+varname+'_'+this.id;
	this.setText(el,varname);
	if(!this.textInstances[varname])this.textInstances[varname]=[];
	this.textInstances[varname].push(el);
	return el;
};
File.prototype.initialize=function(id,data){
	this.id=id;
	this.textInstances=[];
	if(data){
		File_Instances[id]=this;
		File_setData(data,this);
	}
	else x_kfm_getFileDetails(id,File_setData);
};
File.prototype.setText=function(el,varname){
	$j(el).empty();
	var v=$pick(this[varname],'');
	if(varname=='name'){
		if(!kfm_listview && kfm_vars.files.name_length_displayed && kfm_vars.files.name_length_displayed<v.length){
			el.title=v;
			v=v.substring(0,kfm_vars.files.name_length_displayed-3)+'...';
		}
		else if(kfm_listview && kfm_vars.files.name_length_in_list && kfm_vars.files.name_length_in_list<v.length){
			el.title=v;
			v=v.substring(0,kfm_vars.files.name_length_in_list-3)+'...';
		}
	}
	if(varname=='modified' && !v){
		var v=(new Date(this.ctime*1000)).toGMTString().replace(/ GMT$/,'');
		this.modified=v;
	}
	$j(el).text(v);
};
File.prototype.setThumbnailBackground=function(el,reset){
	var fsdata=window.kfm_incrementalFileDisplay_vars.data.sprites;
	var id=this.id;
	if(fsdata){ // sprites exist
		css_sprite=[];
		for(var i=0;i<fsdata.length;++i)if(fsdata[i].files.indexOf(id)!=-1){
			el.style.backgroundImage='url("sprite.php?md5='+fsdata[i].sprite+'")';
			el.style.backgroundPosition=-64*fsdata[i].files.indexOf(id)+'px top';
			return;
		}
	}
	// sprite doesn't exist, or image is not in a CSS sprite
	addRand = "";
	curBG = el.style.backgroundImage;
	if (curBG && curBG.indexOf("&rand1=") > 0) addRand = curBG.substring(curBG.indexOf("&rand1="));
	if (curBG == "" || (selectedFiles && selectedFiles.length>0 && id == selectedFiles[0])) addRand = '&rand1=' + Math.random();
	el.style.background='url(get.php?id='+id+'&width=64&height=64'+kfm_vars.get_params + addRand + ') center top no-repeat transparent';
	
	if (el.childNodes && el.childNodes[0] && (selectedFiles.length>0)) {
		el.childNodes[0].style.background='url(get.php?id='+id+'&width=64&height=64'+kfm_vars.get_params + addRand + ') center top no-repeat transparent'
		el.style.background='';
		el.setAttribute("style" , "background-position: center center");
	} else  {
	  el.parentNode.setAttribute("style" , "background-position: center center");	
	}
	
}
File.prototype.iterateThumbnailQueue=function(){
	if(!File_ThumbnailsQueue.length){
		window.File_ThumbnailsTimeout=null;
		return;
	}
	var el=window.File_ThumbnailsQueue[0][0],id=window.File_ThumbnailsQueue[0][1];
	if(el && el.parentNode && el.parentNode.id=='documents_body'){
		var url='get.php?id='+id+'&width=64&height=64'+kfm_vars.get_params;
		var img=document.createElement('img');
		img.src=url;
		img.style.width=1;
		img.style.height=1;
		$j.event.add(img,'load',function(){
			el.style.backgroundImage='url("'+url+'")';
			var F=File_getInstance(id);
			F.id=id;
			F.icon_loaded=1;
			F.icon_url=url;
			$j(this).remove();
		});
		setTimeout(File.prototype.iterateThumbnailQueue,1);
		kfm.addEl(el,img);
	}
	else setTimeout(File.prototype.iterateThumbnailQueue,1);
	window.File_ThumbnailsQueue.shift();
}
function File_getInstance(id,data){
	id=parseInt(id);
	if(isNaN(id))return;
	if(!File_Instances[id] || data){
		File_Instances[id]=new File();
		File_Instances[id].initialize(id,data);
	}
	return File_Instances[id];
}
function File_setData(el,F){
	var id=+el.id;
	if(!F)F=File_getInstance(id);
	$each(el,function(varvalue,varname){
		F[varname]=el[varname];
		if(!F.textInstances || !F.textInstances[varname])return;
		F.textInstances[varname].each(function(t){
			F.setText(t,varname);
		});
	});
	File_Instances[id]=F;
}
var File_Instances=[];
var File_ThumbnailsQueue=[];
