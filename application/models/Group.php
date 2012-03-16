<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Group extends ActiveRecord\Model
{
	# explicit table name
	static $table_name = 'groups';
	
	# explicit pk since our pk is not "id" 
 	static $primary_key = 'id';
	
	# explicit column names for the sake of readability
	static $alias_attribute = array(
		'GroupID' => 'id',
		'Title' => 'title',
		'Description' => 'description');
	
	static $has_many = array(
		array('group_menu_items'
			 ,'class_name' => 'Group_menu_item'
			 ,'foreign_key' => 'groupid'
			 ,'primary_key' => 'groupid'
			 ),
		array('menu_items'
			 ,'class_name' => 'Menu_item'
			 ,'foreign_key' => 'menuitemid'
			 ,'primary_key' => 'menuitemid'
			 //,'through' => 'group_menu_items'
			 , array('through' => 'group_menu_items', 'foreign_key' => 'menuitemid')
			 )
		);
			//array('through' => 'group_menu_items', 'foreign_key' => 'menuitemid')));
}
	
	
?>