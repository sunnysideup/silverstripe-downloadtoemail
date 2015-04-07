<?php
/**
 * Page for Email Download
 * Easiest changed by extending this page
 * and using the hide_ancestor static to hide this page itself.
 */

class EmailDownloadPage extends Page{

	/**
	 * standard SS Variable
	 */
	private static $description = "Allow the user to download a file through their e-mail.";

	/**
	 * standard SS Variable
	 */
	private static $icon = "downloadtoemail/images/treeicons/EmailDownloadPage";

	/**
	 * standard SS Variable
	 */
	private static $db = array(
		"LinkToThirdPartyDownload" => "Varchar(255)",
		"TitleOfFile" => "Varchar(50)",
		"EmailSubject" => "Varchar(200)",
		"NoAccessContent" => "Varchar(255)",
		"ValidityInDays" => "Float",
		"AllowReRequest" => "Boolean",
		"AllowReRequestLabel" => "Varchar(255)",
		"DeclineReRequestLabel" => "Varchar(255)",
		"ThankYouForRequesting" => "Varchar(255)",
		"ThankYouLink" => "Varchar(255)"
	);

	/**
	 * standard SS Variable
	 */
	private static $has_one = array(
		"DownloadFile" => "File"
	);

	/**
	 * standard SS Variable
	 */
	private static $has_many = array(
		"EmailsSent" => "EmailDownloadPage_Registration"
	);

	/**
	 * standard SS Variable
	 */
	private static $defaults = array(
		"NoAccessContent" => "Sorry, you do not have access to this file right now.  Please request access again.",
		"ThankYouForRequesting" => "Thank you for requesting this download, please check your e-mail for more information ...",
		"AllowReRequest" => true,
		"AllowReRequestLabel" => "Request another copy.",
		"DeclineReRequestLabel" => "You have already requested this file and you can not request it again."
	);

	/**
	 *
	 * @param boolean $includerelations a boolean value to indicate if the labels returned include relation fields
	 *
	 */
	public function fieldLabels($includerelations = true) {
		$labels = parent::fieldLabels($includerelations);
		$labels["TitleOfFile"] = _t("EmailDownloadPage.TITLEOFFILE", "Title of file");
		$labels["LinkToThirdPartyDownload"] = _t("EmailDownloadPage.LINKTOTHIRDPARTYDOWNLOAD", "Link to third-party download file / page");
		$labels["ValidityInDays"] = _t("EmailDownloadPage.VALIDITYINDAYS", "Validity in days (you can use 0.5 for 12 hours, etc...)");
		$labels["DownloadFile"] = $labels["DownloadFileID"] = _t("EmailDownloadPage.DOWNLOADFILE", "Select file to download");
		$labels["ThankYouForRequesting"] = _t("EmailDownloadPage.THANKYOUFORREQUESTING", "Thank you for requesting message");
		$labels["ThankYouLink"] = _t("EmailDownloadPage.THANKYOULINK", "Thank you link");
		$labels["EmailSubject"] = _t("EmailDownloadPage.EMAILSUBJECT", "E-mail Subject");
		$labels["AllowReRequest"] = _t("EmailDownloadPage.ALLOWREREQUEST", "Allow the user to make more than one request for the file (not strictly enforced) - change and reload to see more options");
		$labels["AllowReRequestLabel"] = _t("EmailDownloadPage.ALLOWREREQUESTLABEL", "Label for requesting another copy");
		$labels["DeclineReRequestLabel"] = _t("EmailDownloadPage.DECLINEREREQUESTLABEL", "Explanation of why the user can not request another copy");
		$labels["NoAccessContent"] = _t("EmailDownloadPage.NOACCESSCONTENT", "Content shown when the user does not have access");
		$labels["EmailsSent"] = _t("EmailDownloadPage.EMAILSSENT", "Downloads requested");
		return $labels;
	}

	/**
	 * standard SS Method
	 */
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$labels = $this->fieldLabels(true);
		$fieldsToAdd = array(
			new TextField("TitleOfFile", $labels["TitleOfFile"]),
			$uploadField = new UploadField("DownloadFile", $labels["DownloadFile"])
		);


		if($this->DownloadFileID) {
			$fieldsToAdd = array_merge($fieldsToAdd, array(
				new NumericField("ValidityInDays", $label["ValidityInDays"])
			));
		}
		else {
			$fieldsToAdd = array_merge($fieldsToAdd, array(
				$linkToThirdPartyDownloadField = new TextField("LinkToThirdPartyDownload", $labels["LinkToThirdPartyDownload"])
			));
			$linkToThirdPartyDownloadField->setRightTitle( _t("EmailDownloadPage.LINKTOTHIRDPARTYDOWNLOAD_RIGHT_TITLE","Set this to a third-party website link (e.g. dropbox) - e.g. http://www.mycooldownloadpage.com/mydownloadpage/"));
		}
		$fieldsToAdd = array_merge($fieldsToAdd, array(
			new CheckboxField("AllowReRequest", $labels["AllowReRequest"]),
			new TextField("EmailSubject", $labels["EmailSubject"]),
			new TextField("ThankYouForRequesting", $labels["ThankYouForRequesting"]),
			new TextField("ThankYouLink", $labels["ThankYouLink"])
		));
		if($this->AllowReRequest) {
			$fieldsToAdd = array_merge($fieldsToAdd, array (
				new TextField("AllowReRequestLabel", $labels["AllowReRequestLabel"]),
			));
		}
		else {
			$fieldsToAdd = array_merge($fieldsToAdd, array(
				new TextField("DeclineReRequestLabel", $labels["DeclineReRequestLabel"]),
			));
		}
		$fieldsToAdd = array_merge($fieldsToAdd, array(
			new TextField("NoAccessContent", $labels["NoAccessContent"]),
			$gridField = new GridField("EmailsSent", $labels["EmailsSent"], $this->EmailsSent(), GridFieldConfig_RelationEditor::create())
		));
		$gridField->getConfig()->addComponent(new GridFieldExportButton());
		$fields->addFieldsToTab(
			"Root.DownloadToEmail",
			$fieldsToAdd
		);
		return $fields;
	}

}

class EmailDownloadPage_Controller extends Page_Controller {

	/**
	 * standard SS Variable
	 */
	private static $allowed_actions = array(
		"DownloadForm",
		"dodownload",
		"thankyou",
		"requestrerequest",
		"noaccess"
	);

	/**
	 * Template to be used for sending e-mail.
	 * @var String
	 */
	private static $email_template = "DownloadToEmailEmail";

	/**
	 * Show the download form?
	 * @var Boolean
	 */
	protected $showDownloadForm = true;

	/**
	 * Message to user (e.g. you do not have access to this file)
	 * @var String
	 */
	protected $feedbackMessage = "";

	/**
	 * Type of feedback (Good | Bad | Warning)
	 * @var String
	 */
	protected $feedbackMessageStyle = "";


	/**
	 * Standard SS method
	 */
	public function init(){
		parent::init();
		$this->showDownloadForm = $this->AlreadyRequestedSuccessfully() ? false : true;
	}


	/**
	 *
	 * @return Boolean
	 */
	public function AlreadyRequestedSuccessfully(){
		return Session::get($this->sessionVarNameForSending()) ? true : false;
	}

	public function ReRequestLink(){
		return $this->Link("requestrerequest");
	}

	/**
	 * feedback message for user
	 * @return Varchar
	 */
	public function ShowDownloadForm(){
		return $this->showDownloadForm;
	}

	/**
	 * feedback message for user
	 * @return Varchar
	 */
	public function FeedbackMessage(){
		return DBField::create_field('Varchar', $this->feedbackMessage);
	}

	/**
	 * feedback message for user
	 * @return Varchar
	 */
	public function FeedbackMessageStyle(){
		return DBField::create_field('Varchar', $this->feedbackMessageStyle);
	}

	/**
	 * show the download form.
	 *
	 * @return Form
	 */
	function DownloadForm(){
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript('downloadtoemail/javascript/DownloadToEmail.js');
		$form = new Form(
			$this,
			'DownloadForm',
			new FieldList($emailField = new EmailField('EmailDownloadPageEmail', _t("EmailDownloadPage.EMAIL", "enter your e-mail address"))),
			new FieldList(new FormAction('sendmail', _t("EmailDownloadPage.REQUEST_ACCESS", "request access"))),
			RequiredFields::create(array("EmailField"))
		);
		return $form;
	}

	/**
	 * Sent the e-mail.
	 *
	 * @param Array $data
	 * @param Form $form
	 */
	function sendmail($data, $form) {
		$email = Convert::raw2sql($data["EmailDownloadPageEmail"]);
		$obj = EmailDownloadPage_Registration::get()
			->filter(array("Email" => $email, "DownloadFileID" => $this->DownloadFileID))
			->first();
		if(!$obj) {
			$obj = new EmailDownloadPage_Registration();
			$obj->Email = $email;
			$obj->DownloadFileID = $this->DownloadFileID;
		}
		else {
			$obj->Used = false;
		}
		$obj->write();
		$email = new Email(Email::getAdminEmail(), $data["EmailDownloadPageEmail"], $this->EmailSubject);
		$email->setTemplate($this->config()->get("email_template"));
		// You can call this multiple times or bundle everything into an array, including DataSetObjects
		$email->populateTemplate(
			new ArrayData(
				array(
					"EmailSubject" => DBField::create_field('Varchar', $this->EmailSubject),
					"TitleOfFile" => DBField::create_field('Varchar', $this->TitleOfFile),
					"ValidUntil" => date('Y-M-d', strtotime("+".($this->ValidityInDays * 86400)." seconds")),
					"HasLink" => $this->LinkToThirdPartyDownload ? true : false,
					"HasFile" => $this->DownloadFileID ? true : false,
					"LinkToThirdPartyDownload" => $this->LinkToThirdPartyDownload,
					"File" => $this->DownloadFile(),
					"DownloadLink" => Director::absoluteURL($this->Link("dodownload/".$obj->ID."/".$obj->Code.'/')),
					"FileLocation" => Director::absoluteURL($this->DownloadFile()->Link())
				)
			)
		);
		$outcome = $email->send();
		Session::set($this->sessionVarNameForSending(), $outcome);
		$this->redirect($this->Link("thankyou/".($outcome ? "success" : "fail")."/"));
		return array();
	}

	/**
	 * Do the download itself.
	 * URL should be formatted as
	 * /thankyou/outcome/
	 *
	 * @param HTTPRequest
	 */
	function thankyou($request){
		$outcome = $request->param("ID");
		if($outcome == "success") {
			$this->feedbackMessage = $this->ThankYouForRequesting;
			$this->feedbackMessageStyle = "good";
			$this->showDownloadForm = false;
			$this->DeclineReRequestLabel = "";
		}
		else {
			$this->feedbackMessage = "E-mail could not be sent.";
			$this->feedbackMessageStyle = "bad";
			$this->DeclineReRequestLabel = "";
		}
		return array();
	}

	/**
	 * Do the download itself.
	 * URL should be formatted as
	 * /dodownload/$ID/$CodeForObject/
	 *
	 * @param HTTPRequest
	 */
	function dodownload($request){
		Session::set($this->sessionVarNameForSending(), true);
		$id = intval($request->param("ID"));
		$code = Convert::raw2sql($request->param("OtherID"));
		if($id && $code) {
			$obj = EmailDownloadPage_Registration::get()->filter(
				array(
					"ID" => $id,
					"Code" => $code,
					"Used" => 0
				)
			)->First();
			if($obj) {
				if($this->ValidityInDays) {
					$tsNow = strtotime("NOW");
					$validUntilTs = strtotime($obj->Created." +".(86400 * $this->ValidityInDays)." seconds");
					if($tsNow > $validUntilTs ) {
						return $this->redirect($this->Link("noaccess"));
					}
				}
				$obj->DownloadTimes++;
				$obj->Used = true;
				$obj->write();
				return $this->sendFile($obj->DownloadFile());
			}
		}
		$this->redirect($this->Link("noaccess"));
	}

	/**
	 *
	 * What happens when the person does not have access.
	 */
	public function noaccess(){
		$this->feedbackMessage = $this->NoAccessContent;
		$this->feedbackMessageStyle = "warning";
		return array();
	}

	/**
	 *
	 * What happens when the person does not have access.
	 */
	public function requestrerequest(){
		if($this->AllowReRequest) {
			Session::set($this->sessionVarNameForSending(), false);
			Session::clear($this->sessionVarNameForSending());
			$this->redirect($this->Link());
		}
		else {
			$this->redirect("noaccess");
		}
		return array();
	}

	// We calculate the timelimit based on the filesize. Set to 0 to give unlimited timelimit.
	// The calculation is: give enough time for the user with x kB/s connection to donwload the entire file.
	// E.g. The default 50kB/s equates to 348 minutes per 1GB file.
	private static $min_download_bandwidth = 50; // [in kilobytes per second]

	/**
	 *
	 * COPIED CODE!!!!!
	 *
	 * This is copied from here:
	 * https://github.com/silverstripe-labs/silverstripe-secureassets/blob/master/code/SecureFileController.php
	 *
	 * @param File $file
	 */
	protected function sendFile($file) {
		$path = $file->getFullPath();
		if(SapphireTest::is_running_test()) {
			return file_get_contents($path);
		}
		header('Content-Description: File Transfer');
		// Quotes needed to retain spaces (http://kb.mozillazine.org/Filenames_with_spaces_are_truncated_upon_download)
		header('Content-Disposition: inline; filename="' . basename($path) . '"');
		header('Content-Length: ' . $file->getAbsoluteSize());
		header('Content-Type: ' . HTTP::get_mime_type($file->getRelativePath()));
		header('Content-Transfer-Encoding: binary');
		// Fixes IE6,7,8 file downloads over HTTPS bug (http://support.microsoft.com/kb/812935)
		header('Pragma: ');
		if ($this->config()->min_download_bandwidth) {
			// Allow the download to last long enough to allow full download with min_download_bandwidth connection.
			increase_time_limit_to((int)(filesize($path)/($this->config()->min_download_bandwidth*1024)));
		}
		else {
			// Remove the timelimit.
			increase_time_limit_to(0);
		}
		// Clear PHP buffer, otherwise the script will try to allocate memory for entire file.
		while (ob_get_level() > 0) {
			ob_end_flush();
		}
		// Prevent blocking of the session file by PHP. Without this the user can't visit another page of the same
		// website during download (see http://konrness.com/php5/how-to-prevent-blocking-php-requests/)
		session_write_close();
		readfile($path);
		die();
	}

	/**
	 *
	 * @return String
	 */
	protected function sessionVarNameForSending(){
		return "EmailDownloadPage_Controller_".$this->ID."_Sent";
	}

}
