<?php

class ksf_icalendar
{
	var $data_dictionary = array();			//For translating our fields to the other entities fields
	var $id;
	var $trigger;				//vAlarm
	var $action;				//vAlarm, todo
	var $desciption;			//vAlarm, vJournal
	var $method;				//vEvent
	var $status;				//vEvent, todo, vJournal
	var $sequence;				//vEvent Updates, todo
	var $UID;				//vEvent, todo, vJournal
	var $dtstamp;				//vEvent, todo, vJournal
	var $organizer;				//vEvent, vJournal
	var $dtstart;				//vEvent
	var $dtend;				//vEvent, todo
	var $summary;				//vEvent, todo
	var $due;				//TODO
	var $attach;				//TODO
	var $repeat;				//TODO
	var $duration;				//TODO
	var $class;				//vJournal
	var $categories;			//vJournal
	var $url;				//vFreeBusy
	var $allday;
	var $busystatus;

	function __construct()
	{
	}
	function import_external( $ext_class, $ext_class_name )
	{
		//Use data_dictionary, the class name, and list of fields
		//go through list of fields and check in ext_class to
		//copy to our class
	}
	function export_external( $ext_class, $ext_class_name )
	{
		//Use data_dictionary, the class name, and list of fields
		//go through list of fields and check in this class to
		//copy to their class
	}

