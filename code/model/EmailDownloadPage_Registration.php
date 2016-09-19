<?php


/**
 * record of all the
 *
 */
class EmailDownloadPage_Registration extends DataObject
{
    private static $db = array(
        "Email" => "Varchar",
        "Code" => "Varchar",
        "Used" => "Boolean",
        "DownloadTimes" => "Int"
    );

    private static $has_one = array(
        "DownloadFile" => "File",
        "EmailDownloadPage" => "EmailDownloadPage"
    );

    private static $casting = array(
        "Title" => "Varchar",
        "UsedNice" => "Varchar"
    );

    private static $summary_fields = array(
        "Created" => "Sent",
        "Email" => "Email",
        "UsedNice" => "Download Link Has Been Used",
        "DownloadTimes" => "Times Downloaded"
    );

    private static $searchable_fields = array(
        "Email",
        "UsedNice"
    );

    /**
     * standard SS method
     * @param Member $member
     * @return Boolean
     */
    public function canCreate($member = null)
    {
        return false;
    }

    /**
     * standard SS method
     * @param Member $member
     * @return Boolean
     */
    public function canDelete($member = null)
    {
        return false;
    }

    /**
     * standard SS method
     * @param Member $member
     * @return Boolean
     */
    public function canEdit($member = null)
    {
        return false;
    }

    /**
     * casted variable
     * @return String
     */
    public function getTitle()
    {
        return "Download for ".$this->Email;
    }

    /**
     * casted variable
     * @return String
     */
    public function getUsedNice()
    {
        return $this->dbObject('Used')->Nice();
    }

    /**
     * default sort
     * @var String
     */
    private static $default_sort = "\"Created\" DESC";

    /**
     * Automatically set Code
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->Code) {
            $this->Code = md5(rand(0, 10000));
        }
    }
}
