<?php
/**
 * @version		$Id: maxcdnfiletypes.php 1 2013-08-22 17:56:04Z rafael $
 * @package		jomCDN
 * @copyright	Copyright (C) 2010 'corePHP' / corephp.com. All rights reserved.
 * @license		GNU/GPL, see LICENSE.txt
 */
defined('_JEXEC') or die( 'Restricted access' );
/**
 * Renders a jomcdn Form
 *
 * @package		jomCDN
 */

class JFormFieldmaxcdnfiletypes extends JFormField
{
	/**
	 * Element name
	 *
	 * @access	protected
	 * @var		string
	 */
	public $type = 'maxcdnfiletypes';

	protected function getInput()
	{
		?>
		<script language="JavaScript">
			function jomcdnCheckAll(checkbox,classname)
			{
				checkbox.checked=!(jomcdnAllChecked(classname));
				document.getElements('input.'+classname).each(function(el)
				{
					el.checked=checkbox.checked;
				});
			}
			function jomcdnAllChecked(classname)
			{
				var allchecked=1;
				document.getElements('input.'+classname).each(function(el)
				{
					if(!el.checked)
					{
						allchecked=0;
						return 0;
					}
				});
				return allchecked;
			}
		</script>
	<?php

		$this->params = $this->element->attributes();
		$newlines = $this->def('newlines', 0);
		$showcheckall = $this->def('showcheckall', 0);

		$checkall = ($this->value == '*');

		if (!$checkall)
		{
			if (!is_array($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		$options = array();

		foreach ($this->element->children() as $option)
		{
			if ($option->getName() != 'option')
			{
				continue;
			}

			$text = trim((string) $option);
			$hasval = 0;

			if (isset($option['value']))
			{
				$val = (string) $option['value'];
				$disabled = (int) $option['disabled'];
				$hasval = 1;
			}

			if ($hasval)
			{
				$option = '<input type="checkbox" class="jomcdn_' . $this->id . '" id="' . $this->id . $val . '" name="' . $this->name . '[]" value="' . $val . '"';

				if ($checkall || in_array($val, $this->value))
				{
					$option .= ' checked="checked"';
				}

				if ($disabled)
				{
					$option .= ' disabled="disabled"';
				}

				$option .= ' /> <label for="' . $this->id . $val . '" class="checkboxes">' . JText::_($text) . '</label>';
			}
			else
			{
				$option = '<label style="clear:both;"><strong>' . JText::_($text) . '</strong></label>';
			}

			$options[] = $option;
		}

		$options = implode('', $options);

		if ($showcheckall)
		{
			$checkers = array();

			if ($showcheckall)
			{
				$checkers[] = '<input id="jomcdncheckall_' . $this->id . '" type="checkbox" onclick="jomcdnCheckAll( this, \'jomcdn_' . $this->id . '\' );" /> ' . JText::_('JALL');

				$js = "
					window.addEvent('domready', function() {
						$('jomcdncheckall_" . $this->id . "').checked = jomcdnAllChecked( 'jomcdn_" . $this->id . "' );
					});
				";

				JFactory::getDocument()->addScriptDeclaration($js);
			}

			$options = implode('&nbsp;&nbsp;&nbsp;', $checkers) . '<br />' . $options;
		}

		$options .= '<input type="hidden" id="' . $this->id . 'x" name="' . $this->name . '' . '[]" value="x" checked="checked" />';

		$html = array();
		$html[] = '<fieldset id="' . $this->id . '" class="' . ($newlines ? 'checkboxes' : 'radio') . '">';
		$html[] = $options;
		$html[] = '</fieldset>';

		return implode('', $html);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
