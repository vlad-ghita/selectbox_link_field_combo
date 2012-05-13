<?php

	if( !defined('__IN_SYMPHONY__') ) die('<h2>Symphony Error</h2><p>You cannot directly access this file</p>');

	require_once(EXTENSIONS.'/selectbox_link_field/fields/field.selectbox_link.php');

	Class fieldSelectBox_Link_Combo extends fieldSelectBox_Link
	{


		/*------------------------------------------------------------------------------------------------*/
		/*  Definition  */
		/*------------------------------------------------------------------------------------------------*/

		public function __construct(){
			parent::__construct();
			$this->_name = __('Select Box Link Combo');

			$this->set('parent_field_id', '');
			$this->set('relation_field_id', '');
		}

		public function createTable(){
			return Symphony::Database()->query(
				"CREATE TABLE IF NOT EXISTS `tbl_entries_data_".$this->get('id')."` (
				`id` int(11) unsigned NOT NULL auto_increment,
				`entry_id` int(11) unsigned NOT NULL,
				`relation_id` int(11) unsigned DEFAULT NULL,
				PRIMARY KEY	 (`id`),
				KEY `entry_id` (`entry_id`),
				KEY `relation_id` (`relation_id`)
				) ENGINE=MyISAM;"
			);
		}

		public function canToggle(){
			return false;
		}

		public function commit(){
			if( !Field::commit() ) return false;

			$id = $this->get('id');

			if( $id === false ) return false;

			$fields = array();
			$fields['field_id'] = $id;
			if( $this->get('parent_field_id') != '' ) $fields['parent_field_id'] = $this->get('parent_field_id');
			if( $this->get('related_field_id') != '' ) $fields['related_field_id'] = implode(',', $this->get('related_field_id'));
			if( $this->get('relation_field_id') != '' ) $fields['relation_field_id'] = $this->get('relation_field_id');
			$fields['allow_multiple_selection'] = $this->get('allow_multiple_selection') ? $this->get('allow_multiple_selection') : 'no';
			$fields['show_association'] = $this->get('show_association') == 'yes' ? 'yes' : 'no';
			$fields['limit'] = max(1, (int)$this->get('limit'));

			Symphony::Database()->query("DELETE FROM `tbl_fields_".$this->handle()."` WHERE `field_id` = '{$id}'");

			if( !Symphony::Database()->insert($fields, 'tbl_fields_'.$this->handle()) ) return false;

			SectionManager::removeSectionAssociation($id);

			foreach( $this->get('related_field_id') as $field_id ){
				SectionManager::createSectionAssociation(NULL, $id, $field_id, $this->get('show_association') == 'yes' ? true : false);
			}

			SectionManager::createSectionAssociation(NULL, $id, $this->get('relation_field_id'), $this->get('show_association') == 'yes' ? true : false);

			return true;
		}


		/*------------------------------------------------------------------------------------------------*/
		/*  Settings  */
		/*------------------------------------------------------------------------------------------------*/

		public function checkFields(&$errors, $checkForDuplicates = true){
			Field::checkFields($errors, $checkForDuplicates);

			$parent_field_id = $this->get('parent_field_id');
			if( empty($parent_field_id) ){
				$errors['parent_field_id'] = __('This is a required field.');
			}

			$related_field_id = $this->get('related_field_id');
			if( empty($related_field_id) ){
				$errors['related_field_id'] = __('This is a required field.');
			}

			$relation_field_id = $this->get('relation_field_id');
			if( empty($relation_field_id) ){
				$errors['relation_field_id'] = __('This is a required field.');
			}

			if( is_array($errors) && !empty($errors) ){
				return self::__ERROR__;
			}

			// validate relation:
			// new Parent_Field->Values == new Relation_Field->Values
			else{
				// Parent ID will always be set from current section. Get Parent->Values from Form Data

				$new_parent_values = null;

				foreach( $_REQUEST['fields'] as $field ){
					if( $field['id'] == $parent_field_id ){
						$new_parent_values = $field['related_field_id'][0];
						break;
					}
				}

				if( $new_parent_values == null ){
					$errors['relation_field_id'] = __("Check Values &lt;select&gt; from Parent. It has problems.");
					return self::__ERROR_CUSTOM__;
				}

				// Relation ID will always be set in another section. Get from DB

				$sql_result = Symphony::Database()->fetch("SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE field_id = '{$relation_field_id}'");
				$new_relation_values = $sql_result[0]['related_field_id'];

				if( $new_parent_values != $new_relation_values ){
					$errors['relation_field_id'] = __('<b>Relation validation failed.</b><br />1. Please check Parent and Relation.<br />2. If Relation and Parent are OK, check Values from Parent Field to be OK.');

					return self::__ERROR_CUSTOM__;
				}
			}

			return (is_array($errors) && !empty($errors) ? self::__ERROR__ : self::__OK__);
		}

		public function displaySettingsPanel(&$wrapper, $errors = NULL){
			Field::displaySettingsPanel($wrapper, $errors);

			$sections = SectionManager::fetch(NULL, 'ASC', 'sortorder');

			$field_groups = array();

			if( is_array($sections) && !empty($sections) ){
				foreach( $sections as $section ){
					$section_fields = $section->fetchFields();
					$fields = array();

					foreach( $section_fields as $f ){
						$fields[$f->get('id')] = $f;
					}

					$field_groups[$section->get('id')] = array('fields' => $fields, 'section' => $section);
				}
			}

			$this->appendParentSelect($wrapper, $field_groups, $errors);
			$this->appendValuesSelect($wrapper, $field_groups, $errors);
			$this->appendRelationSelect($wrapper, $field_groups, $errors);
			$this->appendMaximumEntriesInput($wrapper);

			$div = new XMLElement('div', NULL, array('class' => 'compact'));

			$this->appendAllowMultipleCheckbox($div);
			$this->appendShowAssociationCheckbox($div);
			$this->appendRequiredCheckbox($div);
			$this->appendShowColumnCheckbox($div);

			$wrapper->appendChild($div);
		}

		protected function appendParentSelect(XMLElement &$wrapper, $field_groups, $errors = null){
			$label = Widget::Label(__('Parent'));

			$options = array();
			$eligible_parents = $this->_fetchEligibleFields('Parent');
			$section_id = Administration::instance()->Page->_context[1];

			$fields = array();

			if( is_array($eligible_parents) && !empty($eligible_parents) && isset($section_id) ){
				foreach( $eligible_parents as $field_id ){

					if( $field_id != $this->get('id') ){
						$fields[] = array(
							$field_id,
							($field_id == $this->get('parent_field_id')),
							$field_groups[$section_id]['fields'][$field_id]->get('label')
						);
					}
				}
			}

			if( is_array($fields) && !empty($fields) ){
				$options[] = array(
					'label' => $field_groups[$section_id]['section']->get('name'),
					'options' => $fields
				);
			}

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][parent_field_id]', $options));

			if( isset($errors['parent_field_id']) ) $wrapper->appendChild(Widget::Error($label, $errors['parent_field_id']));
			else $wrapper->appendChild($label);
		}

		protected function appendValuesSelect(XMLElement &$wrapper, $field_groups, $errors = null){
			$label = Widget::Label(__('Values'));

			$options = array();
			$this_section_id = Administration::instance()->Page->_context[1];

			$eligible_sections = $this->_fetchEligibleSections();

			if( !empty($eligible_sections) && is_array($eligible_sections) ){

				foreach( $field_groups as $section_id => $group ){
					if( !in_array($section_id, $eligible_sections) || ($this_section_id == $section_id) ) continue;

					$fields = array();

					foreach( $group['fields'] as $f ){
						if( $f->get('id') != $this->get('id') && $f->canPrePopulate() ){
							$fields[] = array(
								$f->get('id'),
								@in_array($f->get('id'), $this->get('related_field_id')),
								$f->get('label')
							);
						}
					}

					if( is_array($fields) && !empty($fields) ) $options[] = array('label' => $group['section']->get('name'), 'options' => $fields);
				}
			}

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][related_field_id][]', $options));

			if( isset($errors['related_field_id']) ) $wrapper->appendChild(Widget::Error($label, $errors['related_field_id']));
			else $wrapper->appendChild($label);
		}

		protected function appendRelationSelect(XMLElement &$wrapper, $field_groups, $errors = null){
			$label = Widget::Label(__('Relation'));

			$options = array();
			$eligible_relations = $this->_fetchEligibleFields('Relation');

			if( !empty($eligible_relations) && is_array($eligible_relations) ){
				foreach( $field_groups as $group ){
					if( !is_array($group['fields']) ) continue;

					$fields = array();

					foreach( $group['fields'] as $f ){
						if( $f->get('id') != $this->get('id') && in_array($f->get('id'), $eligible_relations) ){
							$fields[] = array(
								$f->get('id'),
								($f->get('id') == $this->get('relation_field_id')),
								$f->get('label')
							);
						}
					}

					if( is_array($fields) && !empty($fields) ) $options[] = array('label' => $group['section']->get('name'), 'options' => $fields);
				}
			}

			$label->appendChild(Widget::Select('fields['.$this->get('sortorder').'][relation_field_id]', $options));

			if( isset($errors['relation_field_id']) ) $wrapper->appendChild(Widget::Error($label, $errors['relation_field_id']));
			else $wrapper->appendChild($label);
		}

		protected function appendMaximumEntriesInput(XMLElement &$wrapper){
			$label = Widget::Label();
			$input = Widget::Input('fields['.$this->get('sortorder').'][limit]', (string)$this->get('limit'));
			$input->setAttribute('size', '3');
			$label->setValue(__('Limit to the %s most recent entries', array($input->generate())));
			$wrapper->appendChild($label);
		}

		protected function appendAllowMultipleCheckbox(XMLElement &$div){
			$label = Widget::Label(null, null, 'column');
			$input = Widget::Input('fields['.$this->get('sortorder').'][allow_multiple_selection]', 'yes', 'checkbox');
			if( $this->get('allow_multiple_selection') == 'yes' ) $input->setAttribute('checked', 'checked');
			$label->setValue($input->generate().' '.__('Allow selection of multiple options'));
			$div->appendChild($label);
		}

		/**
		 * Finds eligible fields to populate <options>s of <select>s.
		 *
		 * @param string $select
		 *  Desired select element.
		 *
		 * @return array
		 */
		private function _fetchEligibleFields($select){
			if( !is_string($select) || empty($select) ) return false;

			$section_id = Administration::instance()->Page->_context[1];
			$thisid = $this->get('id');

			$result = array();

			$sql_where = 'AND (type = "selectbox_link" OR type = "selectbox_link_combo")';
			if( !empty($thisid) ){
				$sql_where .= ' AND id != '.$thisid;
			}

			$fm = new FieldManager(Symphony::Engine());
			$fields = $fm->fetch(NULL, $section_id, 'ASC', 'sortorder', NULL, NULL, $sql_where);

			if( !empty($fields) && is_array($fields) ){

				switch( $select ){
					case 'Parent':
						$result = $this->_fetchEligibleFieldsParent($fields);
						break;

					case 'Values':
						$result = $this->_fetchEligibleFieldsValues();
						break;

					case 'Relation':
						$result = $this->_fetchEligibleFieldsRelation($fields);
						break;
				}
			}

			return (array)$result;
		}

		private function _fetchEligibleFieldsParent($fields){
			$result = array();
			$sql_where = '';

			foreach( $fields as $field ){
				$sql_where .= (!empty($sql_where)) ? " OR " : '';
				$sql_where .= "field_id = ".$field->get('id').' ';
			}
			$sql_where = 'WHERE '.$sql_where;

			$arr_sbl = Symphony::Database()->fetch("SELECT `field_id` FROM `tbl_fields_selectbox_link` {$sql_where}");
			$arr_sblc = Symphony::Database()->fetch("SELECT `field_id` FROM `tbl_fields_selectbox_link_combo` {$sql_where}");

			$arr_all = array_merge($arr_sbl, $arr_sblc);

			foreach( $arr_all as $field ){
				$result[$field['field_id']] = $field['field_id'];
			}

			return $result;
		}

		private function _fetchEligibleFieldsValues(){
			$result = array();

			$arr_sbl = Symphony::Database()->fetch("SELECT `related_field_id` FROM `tbl_fields_selectbox_link` WHERE allow_multiple_selection = 'no'");
			$arr_sblc = Symphony::Database()->fetch("SELECT `related_field_id` FROM `tbl_fields_selectbox_link_combo` WHERE allow_multiple_selection = 'no'");

			$arr_all = array_merge($arr_sbl, $arr_sblc);

			foreach( $arr_all as $field ){
				$result[$field['related_field_id']] = $field['related_field_id'];
			}

			return $result;
		}

		private function _fetchEligibleFieldsRelation($fields){
			$result = array();
			$sql_where = '';

			foreach( $fields as $field ){
				$sql_where .= (!empty($sql_where)) ? " AND " : '';
				$sql_where .= "field_id != ".$field->get('id').' ';
			}
			$sql_where = 'WHERE '.$sql_where;

			$arr_sbl = Symphony::Database()->fetch("SELECT `field_id` FROM `tbl_fields_selectbox_link` {$sql_where}");

			foreach( $arr_sbl as $field ){
				$result[$field['field_id']] = $field['field_id'];
			}

			return $result;
		}

		private function _fetchEligibleSections(){
			$sections = Symphony::Database()->fetch("SELECT `parent_section` FROM `tbl_fields` WHERE type = 'selectbox_link' OR type = 'selectbox_link_combo'");

			$result = array();

			foreach( $sections as $section ){
				$result[$section['parent_section']] = $section['parent_section'];
			}

			return (array)$result;
		}


		/*------------------------------------------------------------------------------------------------*/
		/*  Publish  */
		/*------------------------------------------------------------------------------------------------*/

		public function displayPublishPanel(&$wrapper, $data = NULL, $flagWithError = NULL, $fieldnamePrefix = NULL, $fieldnamePostfix = NULL){

			$entry_ids = array();

			if( !is_null($data['relation_id']) ){
				if( !is_array($data['relation_id']) ){
					$entry_ids = array($data['relation_id']);
				}
				else{
					$entry_ids = array_values($data['relation_id']);
				}
			}

			$states = $this->findOptions($entry_ids);
			$options = array();

			if( $this->get('required') != 'yes' ) $options[] = array(NULL, false, NULL);

			if( !empty($states) ){
				foreach( $states as $s ){
					$group = array(
						'label' => $s['name'],
						'options' => array()
					);

					foreach( $s['values'] as $id => $v ){
						$group['options'][] = array(
							$id,
							in_array($id, $entry_ids),
							General::sanitize($v['value']),
							null,
							null,
							array(
								'data-parent' => $this->get('parent_field_id'),
								'data-selector' => General::sanitize($v['parent_id'])
							)
						);
					}
					$options[] = $group;
				}
			}

			$fieldname = 'fields'.$fieldnamePrefix.'['.$this->get('element_name').']'.$fieldnamePostfix;
			if( $this->get('allow_multiple_selection') == 'yes' ) $fieldname .= '[]';

			$label = Widget::Label($this->get('label'));
			$label->appendChild(Widget::Select($fieldname, $options, ($this->get('allow_multiple_selection') == 'yes' ? array('multiple' => 'multiple') : NULL)));

			if( $flagWithError != NULL ) $wrapper->appendChild(Widget::Error($label, $flagWithError));
			else $wrapper->appendChild($label);
		}

		public function findOptions(array $existing_selection = NULL){
			$values = array();
			$limit = $this->get('limit');

			// find the sections of the related fields
			$sections = Symphony::Database()->fetch("SELECT DISTINCT (s.id), s.name, f.id as `field_id`
				 								FROM `tbl_sections` AS `s`
												LEFT JOIN `tbl_fields` AS `f` ON `s`.id = `f`.parent_section
												WHERE `f`.id IN ('".implode("','", $this->get('related_field_id'))."')
												ORDER BY s.sortorder ASC");

			// build a list of entries associated with their parent relations
			$parent_relations = $this->_fetchRelationsFromRelationID();

			if( is_array($sections) && !empty($sections) ){
				foreach( $sections as $section ){

					$group = array(
						'name' => $section['name'],
						'section' => $section['id'],
						'values' => array()
					);

					// build a list of entry IDs with the correct sort order
					$em = new EntryManager(Symphony::Engine());
					$entries = $em->fetch(NULL, $section['id'], $limit, 0, null, null, false, false);

					$results = array();
					foreach( $entries as $entry ){
						$results[] = (int)$entry['id'];
					}

					// if a value is already selected, ensure it is added to the list (if it isn't in the available options)
					if( !is_null($existing_selection) && !empty($existing_selection) ){
						$entries_for_field = $this->findEntriesForField($existing_selection, $section['field_id']);
						$results = array_merge($results, $entries_for_field);
					}

					if( is_array($results) && !empty($results) ){
						$related_values = $this->findRelatedValues($results);

						foreach( $related_values as $value ){
							$group['values'][$value['id']]['value'] = $value['value'];
							$group['values'][$value['id']]['parent_id'] = $parent_relations[$value['id']];
						}
					}

					$values[] = $group;
				}
			}

			return $values;
		}

		private function _fetchRelationsFromRelationID(){
			$query = "SELECT `entry_id`, `relation_id` FROM `tbl_entries_data_{$this->get('relation_field_id')}`";
			$result = Symphony::Database()->fetch($query, 'entry_id');

			$relations = array();
			foreach( $result as $key => $value ){
				$relations[$key] = $value['relation_id'];
			}

			return $relations;
		}
	}
