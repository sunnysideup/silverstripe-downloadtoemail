<?php


class EmailDownloadPage extends Page{

	private static $icon = "mysite/images/treeicons/EmailDownloadPage";

	private static $db = array(
		"TitleOfFile" => "Varchar",
		"EmailSubject" => "Varchar"
	);

	private static $has_one = array(
		"DownloadFile" => "File"
	);

	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab("Root.File", $textField = new TextField("EmailSubject"));
		$fields->addFieldToTab("Root.File", $textField = new TextField("TitleOfFile"));
		$fields->addFieldToTab("Root.File", $uploadField = new UploadField("DownloadFile"));
		return $fields;
	}

}
class EmailDownloadPage_Controller extends Page_Controller {

	private static $allowed_actions = array(
		"DownloadForm",
		"sendmail",
		"dodownload"
	);

	function DownloadForm(){
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('mysite/javascript/DownloadForm.js');
		$form = new Form(
			$this,
			'DownloadForm',
			new FieldList($emailField = new EmailField('Email')),
			new FieldList(new FormAction('sendmail', "download ".$this->TitleOfFile))
		);
		$emailField->setAttribute("placeholder", "Your E-mail");
		//$form->setFormMethod('GET');
		$form->disableSecurityToken();
		$form->loadDataFrom($_GET);
		//$searchField->setAttribute("autocomplete", "off");
		//$form->setAttribute("autocomplete", "off");
		return $form;
	}

	function sendmail($data, $form) {
		$email = Convert::raw2sql($data["Email"]);
		$obj =EmailDownloadPage_Registration::get()->filter(array("Email" => $email))->first();
		if(!$obj) {
			$obj = new EmailDownloadPage_Registration();
			$obj->Email = $email;
		}
		$obj->Code = "";
		$obj->write();
		$email = new Email(Email::getAdminEmail(), $data["Email"], $this->EmailSubject, $body);
		$email->setTemplate('DownloadFormEmail');
		// You can call this multiple times or bundle everything into an array, including DataSetObjects
		$email->populateTemplate(
			new ArrayData(
				array(
					"EmailSubject" => DBField::create_field('Varchar', $this->EmailSubject),
					"TitleOfFile" => DBField::create_field('Varchar', $this->TitleOfFile),
					"ValidUntil" => date('Y-M-d', strtotime("+3 days")),
					"File" => $this->DownloadFile(),
					"DownloadLink" => $this->Link("dodownload"),
					"FileLocation" => Director::absoluteURL(DBField::create_field('Varchar', $this->DownloadFile()->Link()))
				)
			)
		);
		$email->send();
	}

	function dodownload($request){

	}


}

class EmailDownloadPage_Registration extends DataObject {

	private static $db = array(
		"Title" => "Varchar",
		"Code" => "Varchar"
	);

	private static $default_sort = "\"Created\" DESC";

}
