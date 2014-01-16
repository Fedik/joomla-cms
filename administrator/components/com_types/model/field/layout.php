<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_types
 *
 * @copyright   Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Field Layout class for the UCM Types component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_types
 */
class JFormFieldLayout extends JFormField
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	protected $type = 'Layout';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 */
	protected function getInput()
	{
		// fake Input for demonstartion

		$attr = '';
		$values = $this->value ? $this->value : array('name' => '', 'params' => '');

		// some fake but possible layouts
		$layouts = array(
				'' => array( 'title' => 'Default'),
				'heading'=> array(
					'title' => 'Heading',
					'form' => 'layout_params_heading',
				),
				'link' => array( 'title' => 'Link'),
				//'link_modal' => array( 'title' => 'Link Modal'),
				'date' => array(
					'title' => 'Date',
					'form' => 'layout_params_date',
				),
				'image' => array(
					'title' => 'Image',
					'form' => 'layout_params_image',
				),
				//'image_modal' => array( 'title' => 'Image Modal'),
		);

		$options = array();
		$layout_forms = array();
		foreach($layouts as $n => $data) {
			// select list
			$options[] = JHtml::_('select.option', $n, $data['title']);

			// Build subform
			if(!empty($data['form']))
			{
				try {
					$layout_params = empty($values['params'][$n]) ? array() : $values['params'][$n];
					$form = JForm::getInstance($data['form'], $data['form'], array());

					// Reset if there any data cached
					$form->reset();
					// Bind valuse
					$form->bind($layout_params);

					//TODO: no validation, it is bad (!!!)

					// Render the subform
					$layout_form = '<div class="layout-params-form ' . $n . '">';
					foreach($form->getFieldset('params') as $field) {
						// count new id
						$field->id = $this->id . '_params_' . $field->id;

						// cound new name
						//FIXME: no group support (!!!), not so bad but it can make a problem
						$field->name = $this->name . '[params][' . $n . '][' . $field->name . ']';

						// Render input
						$layout_form .= $field->getLabel();
						$layout_form .= $field->getInput();
					}
					$layout_form .= '</div>';
					$layout_forms[] = $layout_form;
				} catch (Exception $e) {
					var_dump($e);
				}

			}
		}

		$html = '<div class="layout-params">';
		$html .= JHtml::_('select.genericlist', $options, $this->name . '[name]', $attr, 'value', 'text', $values['name'], $this->id);
		$html .= implode("\n", $layout_forms);
		$html .= '</div>';

		//some script
		static $added;
		if(!$added)
		{
			JFactory::getDocument()->addScriptDeclaration('
(function($){
 $(document).ready(function(){
	$(".layout-params").each(function(){
		var $lp = $(this);
		var $lf = $lp.children(".layout-params-form").hide();
		var $ls = $lp.children("select");
		var value = $ls.val();
		if(value) $lf.filter("." + value).show();

		$lp.children("select").bind("change", function(){
		  var value = $(this).val();
		  $lf.hide();
		  if(value) $lf.filter("." + value).slideDown();
		});
	});

 });
})(jQuery);
');
			$added = true;
		}

		return $html;
	}
}
