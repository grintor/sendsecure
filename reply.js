KindEditor.ready(function(K) {
	window.editor = K.create('#kind-editor', {
		width : '100%',
		height : '500px',
		resizeType : '0',
		newlineTag : 'br',
		afterUpload : function(url) { alert(url) },
		items : [
			'undo', 'redo', '|', 'cut', 'copy', 'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', '|', 'pagebreak', '/',
			'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|', 'table', 'hr', 'emoticons', 'link', 'unlink'
		]
	})
	window.editor.focus();
	window.editor.html(document.getElementById('message').value);
});

function print1(){
	i = document.getElementsByClassName('ke-toolbar');
	s = i[0].style.display;
	i[0].style.display = 'none';
	window.print();
	i[0].style.display = '';
}

function addAttach(){
	document.getElementById('remove-attach').style.display = 'inline';
	document.getElementById('attachments').style.display = 'inline';
	node = document.createElement('input');
	node.type = 'file';
	node.name = Date.now();
	node.style.visibility = 'hidden';
	node.style.width = '0px';
	node.style.height = '0px';
	node.click();
	node.addEventListener("change", function() { changedAttach(node) });
	document.getElementById('attachments').appendChild(node);
}

function removeAttach(){
	document.getElementById('attachments').style.display = 'none';
	document.getElementById('remove-attach').style.display = 'none';
	var attachments = document.getElementById('attach-names');
	while (attachments.firstChild) {
		attachments.removeChild(attachments.firstChild);
	}
}

function send(){
	node = document.createElement('input');
	node.type = 'hidden';
	node.name = 'message';
	node.value = window.editor.html();
	document.forms[0].appendChild(node);
	document.forms[0].submit();
}

// a callback when the file attachment is selected
function changedAttach(node){
	// convert filepath to filename
	file = node.value.split(/(\\|\/)/g).pop();

	// if there are already elements in 'attach-names'
	if(document.getElementById('attach-names').firstChild) {
		file = " | " + file;
	} else {
		file = file;
	}
	
	span = document.createElement('span');
	span.style.whiteSpace = 'pre';
	span.innerText = file;
	document.getElementById('attach-names').appendChild(span);
}
