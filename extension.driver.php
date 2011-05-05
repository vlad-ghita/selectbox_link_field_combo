<?php

	Class extension_selectbox_link_field_combo extends Extension{

		public function about(){
			return array(
				'name' => 'Field: Select Box Link Combo',
				'version' => '1.0beta',
				'release-date' => '2011-05-04',
				'author' => array(
					'name' => 'Vlad Ghita',
					'email' => 'vlad_micutul@yahoo.com'
				)
			);
		}
		
		public function getSubscribedDelegates(){
			return array(
				array(
					'page' => '/administration/',
					'delegate' => 'AdminPagePreGenerate',
					'callback' => 'appendAssets'
				)
			);
		}

		public function appendAssets($context){
			$callback = Administration::instance()->getPageCallback();
			if (	$callback['driver'] == 'publish' 
					&& in_array($callback['context']['page'], array('new', 'edit'))
				) {
				Administration::instance()->Page->addScriptToHead(URL . '/extensions/selectbox_link_field_combo/assets/selectbox_link_field_combo.publish.js', 100);
			}
		}
		
		public function uninstall(){
			if(parent::uninstall() == true){
				Symphony::Database()->query("DROP TABLE `tbl_fields_selectbox_link_combo`");
				return true;
			}

			return false;
		}

		public function install(){

			try{
				Symphony::Database()->query("CREATE TABLE IF NOT EXISTS `tbl_fields_selectbox_link_combo` (
					  `id` int(11) unsigned NOT NULL auto_increment,
					  `field_id` int(11) unsigned NOT NULL,
					  `allow_multiple_selection` enum('yes','no') NOT NULL default 'no',
					  `show_association` enum('yes','no') NOT NULL default 'yes',
					  `related_field_id` VARCHAR(255) NOT NULL,
					  `parent_field_id` int(11) unsigned NOT NULL,
					  `relation_id` int(11) unsigned NOT NULL,
					  `limit` int(4) unsigned NOT NULL default '20',
				  PRIMARY KEY  (`id`),
				  KEY `field_id` (`field_id`)
				)");
			}
			catch(Exception $e){
				return false;
			}

			return true;
		}

	}
