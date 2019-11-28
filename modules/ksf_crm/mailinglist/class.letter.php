<?php

class letter
{
	var $title;
	var $categories = array();
	var $images = array();
	var $id;
	var $status;
	var $from;
	var $replyto;
	var $to;
	var $body;
	var $formatted_email;
	var $b_formatted;
	var $unsubscribe_link;
	function __construct()
	{
	}
	function getPostTitle()
	{
		return $this->title;
	}
	function getPostCategories()
	{
		return $this->categories;
	}
	function getImages()
	{
		return $this->images;
	}
	function getIndex()
	{
		return $this->id;
	}
	function getStatus()
	{
		return $this->status;
	}
	function setPostTitle($title)
	{
		 $this->title = $title;
	}
	function setPostCategories(/*array*/ $categories)
	{
		 $this->categories = $categories;
	}
	function setImages(/*array*/ $images)
	{
		 $this->images = $images;
	}
	function setIndex( $id )
	{
		 $this->id = $id;
	}
	function setStatus( $status )
	{
		 $this->status = $status;
	}
	function addImage( $image )
	{
		$imgarray = $this->getImages();
		$imgarray[] = $image;
		$this->setImages( $imgarray );
	}
	function addCategories( $categories )
	{
		$categoriesarray = $this->getCategories();
		$categoriesarray[] = $categories;
		$this->setCategories( $categoriesarray );
	}
	function get_from()
	{
		return $this->from;
	}
	function set_from( $from )
	{
		$this->from = $from;
	}
	function get_replyto()
	{
		return $this->replyto;
	}
	function set_replyto( $replyto )
	{
		$this->replyto = $replyto;
	}
	function get_to()
	{
		return $this->to;
	}
	function set_to( $to )
	{
		$this->to = $to;
	}
	function get_body()
	{
		return $this->body;
	}
	function set_body( $body )
	{
		$this->body = $body;
	}
	function get_b_formatted()
	{
		return $this->b_formatted;
	}
	function set_b_formatted( $b_formatted )
	{
		$this->b_formatted = $b_formatted;
	}
	function get_formatted_email()
	{
		return $this->formatted_email;
	}
	function set_formatted_email( $formatted_email )
	{
		$this->formatted_email = $formatted_email;
	}
	function get_unsubscribe_link()
	{
		return $this->unsubscribe_link;
	}
	function set_unsubscribe_link( $unsubscribe_link )
	{
		$this->unsubscribe_link = $unsubscribe_link;
	}
}
