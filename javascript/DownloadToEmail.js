jQuery(document).ready(
	function(){
		DownloadToEmail.init()
	}
);


var DownloadToEmail = {

	holderSelector: ".downloadToEmail",
	linkSectionSelector: ".downloadLink",
	formSectionSelector: ".downloadForm",

	/**
	 * close the download button link and
	 * open the form...
	 *
	 */
	init: function(){
		jQuery(DownloadToEmail.holderSelector).on(
			"click",
			DownloadToEmail.linkSectionSelector+ " a",
			function(event){
				event.preventDefault();
				jQuery(this)
					.parents(DownloadToEmail.holderSelector)
					.find(DownloadToEmail.formSectionSelector)
					.show();
				jQuery(this)
					.parents(DownloadToEmail.linkSectionSelector)
					.hide();
			}
		)
	}
}

//straight away!
jQuery(DownloadToEmail.formSectionSelector).hide();
