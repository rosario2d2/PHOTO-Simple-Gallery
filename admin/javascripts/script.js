$(document).ready(function() {
	
	// Mobile menu
	$("#menu-icon").click(function () {
		if($('#header ul').css('display')=='none'){
			$('#header ul').show();
		}else{
			$('#header ul').hide();
		}
	});
		
	// SEARCH SELECT WORD OR DATE
    $('#search-selector').change(function(){
		$( "#search-selector option:selected").each(function(){
			if($(this).attr("value")=="word"){
				$("#search-by-date").hide();
                $("#search-by-keyword").show();
			}
			if($(this).attr("value")=="date"){
				$("#search-by-keyword").hide();
				$("#search-by-date").show();
			}
		});
    });
    
    // DATEPICKER
    $( ".datepicker" ).datepicker({
		dateFormat: "yy-mm-dd"
		});
		
	// SELECT ALL CHECKBOXES
	$('#selectall').click(function(event) {
		if(this.checked) {
			$('.checkbox').each(function() {
				this.checked = true;
			});
		}else{
			$('.checkbox').each(function() {
				this.checked = false;                  
			});        
		}
	});
	  
	// UPLOAD PAGE
	var selDiv = "";
	var storedFiles = [];
	
	var totalFiles = 0;
	var totalSize = 0;
	
	var allowedExtensions = ['jpg', 'jpeg', 'gif', 'png'];
	
	$("#files").on("change", handleFileSelect);
	
	selDiv = $("#selected-files"); 
	
	$("#upload-form").on("submit", handleForm);
		
	$("body").on("click", ".remove", removeFile);
  
	// Human readable size
	function bytesToSize(bytes) {
	   if(bytes == 0) return '0 Byte';
	   var k = 1000;
	   var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
	   var i = Math.floor(Math.log(bytes) / Math.log(k));
	   return (bytes / Math.pow(k, i)).toPrecision(3) + ' ' + sizes[i];
	}
	
	// Remove file extension 
	function removeExtension(filename) {
		var lastDotPosition = filename.lastIndexOf(".");
		if (lastDotPosition === -1) return filename;
		else return filename.substr(0, lastDotPosition);
	}
	
	// Get file extension
	function getExtension(filename) {
		return filename.split('.').pop().toLowerCase();
	}
	
	// Is alphanumeric
	function alphanumeric(string) {
		var letters = /^[\w\-\_\s]*$/;
		if (letters.test(string)) {
			return true;
		} else {
			return false;
		}
	}	
	
	// Select Files
	function handleFileSelect(e) {
		var files = e.target.files;
		var filesArr = Array.prototype.slice.call(files);
		var counter = 0;
		filesArr.forEach(function(f) {	
			
			var extension = getExtension(f.name);

			if(!f.type.match("image.*")) {
				document.getElementById('totalfiles').innerHTML = 'Error: File is not an image.';
				return;
			} else if($.inArray(extension, allowedExtensions) == -1) {
				document.getElementById('totalfiles').innerHTML = 'Error: Image type non allowed.';
				return;
			}
			
			var reader = new FileReader();
			reader.onload = function (e) {
				
				storedFiles.push(f);
				
				var html = "<tr><td class='table-td-img'><div class='thumbnailsquare'><img src=\"" + e.target.result + "\" data-file='"+f.name+"' class='selFile'></div></td><td class='td-upload-size'>" + bytesToSize(f.size) + "</td><td class='td-upload-name'><input type='text' id='photoname' name='photoname[]' placeholder='" + removeExtension(f.name) + "'></td><td class='td-upload-caption'><input type='text' id='caption' name='caption[]' placeholder='Caption'></td><td><button class='remove'>Remove</button></td></tr>";
				selDiv.prepend(html);
				
				totalFiles++;
				totalSize += f.size;
				
				document.getElementById('totalfiles').innerHTML = 'Files ' + totalFiles + ' Size ' + bytesToSize(totalSize);
				
			}
			reader.readAsDataURL(f); 
		});
	}
	
	// Upload Files
	function handleForm(e) {
		e.preventDefault();
		
		if (storedFiles === undefined || storedFiles.length == 0) {
			document.getElementById('totalfiles').innerHTML = 'Choose an image to upload first.';
			return;
		}
		
		var progress = document.getElementById('_progress');
		
		var photoname = $("input[id='photoname']").map(function(){return $(this).val();}).get();
		photoname = photoname.reverse();
		
		var caption = $("input[id='caption']").map(function(){return $(this).val();}).get();
		caption = caption.reverse();
		
		for(var i=0, len=photoname.length; i<len; i++) {
			if(alphanumeric(photoname[i]) == true) {
			} else {
				document.getElementById('totalfiles').innerHTML = 'Name Fields must be alphanumeric strings';
				return;
			}
		}
		
		for(var i=0, len=caption.length; i<len; i++) {
			if(alphanumeric(caption[i]) == true) {
			} else {
				document.getElementById('totalfiles').innerHTML = 'Caption Fields must be alphanumeric strings';
				return;
			}
		}

		var data = new FormData();
		
		for(var i=0, len=photoname.length; i<len; i++) {
			data.append('photoname[]', photoname[i]);
		}
		
		for(var i=0, len=caption.length; i<len; i++) {
			data.append('caption[]', caption[i]);
		}
	
		var album = document.getElementById("album-select").value;
		data.append('album', album);
		
		for(var i=0, len=storedFiles.length; i<len; i++) {
			data.append('files[]', storedFiles[i]);	
		}
		
		var xhr = new XMLHttpRequest();
		
		$('#progressbar').fadeIn(1000);
		
		xhr.upload.addEventListener("progress", function (evt) {
			if (evt.lengthComputable) {
			  var progress = Math.round(evt.loaded * 100 / evt.total);
			  $("#progressbar").progressbar("value", progress);
			}
		}, false);
		
		
		xhr.open('POST', 'upload.php', true);
		
		xhr.onload = function(e) {
			
			storedFiles = [];
			totalFiles = 0;
			totalSize = 0;
				
			$('#selected-files').find('tr').fadeOut( 1500, function() { $(this).remove(); });
			
			if(this.status == 200) {
				$('#album-select option').prop('selected', function() {
					return this.defaultSelected;
				});
				document.getElementById('totalfiles').innerHTML = 'File upload successfull';
			} else {
				var error = JSON.parse(this.responseText);
				document.getElementById('totalfiles').innerHTML = 'Error: ' + error;
			}
		}
		
		xhr.send(data);
		
		$("#progressbar").progressbar({
		  max: 100,
		  change: function (evt, ui) {
			$("#progresslabel").text($("#progressbar").progressbar("value") + "%");
		  },
		  complete: function (evt, ui) {
			$("#progresslabel").text("Complete");
		  }
		});
    
	}
	
	// Remove Files
	function removeFile(e) {
		var file = $(this).closest('tr').find('img').data("file");
		for(var i=0;i<storedFiles.length;i++) {
			if(storedFiles[i].name === file) {
				totalSize -= storedFiles[i].size;
				storedFiles.splice(i,1);
				break;
			}
		}
		totalFiles--;	
		document.getElementById('totalfiles').innerHTML = 'Files ' + totalFiles + ' Size ' + bytesToSize(totalSize);
		$(this).closest('tr').remove();
	}  
	
});

