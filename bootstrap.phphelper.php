<?php
/**
 * TwitterBootstrapPHPHelper Class
 *
 * Helper class to create HTML that's nicely compliant with Twitter Bootstrap v2.3.2 -- http://twitter.github.io/bootstrap/
 * Intended to work with Font Awesome -- http://fortawesome.github.io/Font-Awesome/
 *
 * @author Leith Caldwell
 * @copyright Copyright (c) 2013, Leith Caldwell
 * @license http://creativecommons.org/licenses/by-sa/3.0/deed.en_US CC BY-SA 3.0
 * @version 0.6
 */
class TwitterBootstrapPHPHelper {
	var $content;
	var $store_html = false;
	var $current_form_tag = 'form';
	var $current_active_tab = array();
	
	function __construct($store_html = true) {
		$content = '';
		$this->store_html = $store_html;
	}

	function render($return = true) {
		$content = $this->content;
		$this->content = '';
		if ($return) return $content;
		else echo $content;
	}
	
	function add($html) {
		$this->store((string) $html);
	}

	/**
	 * Helper functions 
	 *   apply_defaults : apply default options to a given set of input options
	 *   store : store the supplied HTML in the class $content if appropriate
	 *   tag : make an HTML tag
	 *   tag_open : open an HTML tag
	 *   parse_html_options : convert an array to a set of HTML attributes
	 *   id_for_name : convert name containing [] into unique id
	 */
	private static function apply_defaults($opts, $defaults = array()) {
		if (!is_object($opts)) $opts = (object)$opts;
		foreach ($defaults as $opt => $value) {
			if (!isset($opts->$opt)) $opts->$opt = is_array($value) && isset($value['value']) ? $value['value'] : $value;
			else if (is_array($value)) {
				// TODO : add $value['validate'] check?
				if (!empty($value['prefix'])) $opts->$opt = $value['value'].$opts->$opt;
			} 
		}
		return $opts;
	}
	private function store($html, $opts = array()) {
		if (!is_object($opts)) $opts = (object)$opts;
		if ($this->store_html && empty($opts->return_only)) $this->content .= $html;
	}
	public static function tag($tag, $content, $attrs = array()) { return self::tag_open($tag, $attrs).trim($content)."</$tag>"; } 
	public static function tag_open($tag, $attrs = array()) { return "<$tag".self::parse_html_options($attrs).">"; }
	private static function parse_html_options($attributes = array()) {
		if (!$attributes) return;
		$out = '';
		foreach($attributes as $attribute => $value) 
			if ($value != null) $out .= ' '.$attribute.'="'.htmlspecialchars($value, ENT_QUOTES).'"';
		return $out;
	}
	public static function id4name($name, $value = null) { return self::IdForname($name,$value); }
	public static function id_for_name($name, $value = null) {
		$new_name = str_replace(array('[', ']'), array('_', ''), $name);
		if ($value !== null) $new_name .= (substr($new_name, -1) == '_' ? '' : '_').$value;
		return htmlspecialchars($new_name, ENT_QUOTES);
	}
	/* explain() isn't strictly Bootstrap, but provides a nice (?) icon for use with JS .popover() */
	public static function explain($content, $opts = array()) {
		$html = '';
		if (trim($content) != '') {
			$opts = self::apply_defaults($opts, array(
				'class' => array('value' => 'explain ', 'prefix' => true),
			));

			$html = self::tag('a', "<i class='icon-question-sign'>&nbsp;</i>", array(
				'class' => $opts->class,
				'title' => "What's this?",
				'data-content' => $content,
			));
		}
		return $html;
	}

	/**
	 * Create a bootstrap control group.
	 * $label, $controls, and $extra expect fully formed HTML 
	 */
	public function control_group($label, $controls, $class = '', $extra = null)
	{
		$html = self::control_group_open($label, $class);
		$html .= $controls;
		$this->store($controls);
		$html .= self::control_group_close($extra);
		return $html;
	}
	public function control_group_open($label = '', $class = '') {
		$html = '<div class="control-group'.(trim($class) !== '' ? ' '.trim($class) : '').'">';
		if (trim($label) !== '') $html .= '<label class="control-label">'.trim($label).'</label>';
		$html .= '<div class="controls">';
		$this->store($html);
		return $html;
	}
	public function control_group_close($extra = null) {
		$html = (empty($extra) ? '' : trim($extra)) . '</div></div>';
		$this->store($html);
		return $html;
	}

	/* help text that appears arund form controls */
	public function help($content, $opts = array()) {
		$opts = self::apply_defaults($opts, array(
			'inline' => true,
		));
		$attrs = array('class' => 'help-'.(empty($opts->inline) ? 'block' : 'inline'));
		return self::tag('span', $content, $attrs);
	}

	/* headings */
	private function _heading($tag, $text, $opts = array()) { 
		$opts = self::apply_defaults($opts, array(
			'class' => array('value' => 'heading ', 'prefix' => true),
			'explain' => '', 
		));
		if (trim($opts->explain) != '') $opts->explain = " ".self::explain($opts->explain, array('class' => 'heading-explain'));

		$html = self::tag($tag, $text.$opts->explain, array('class' => $opts->class)); 
		$this->store($html, $opts);
		return $html; 
	}
	public function page_heading($text, $opts = array()) { return self::_heading('h1', $text, $opts); }
	public function heading($text, $opts = array())	     { return self::_heading('h2', $text, $opts); }
	public function subheading($text, $opts = array())   { return self::_heading('h3', $text, array('class' => 'subheading'.(isset($opts['class']) ? ' '.$opts['class'] : '')) + $opts); }


	/* standard tags */
	public function p($text, $opts = array()) { $html = self::tag('p', $text, $opts); $this->store($html, $opts); return $html; }
	public function h4($text, $opts = array()) { $html = self::tag('h4', $text, $opts); $this->store($html, $opts); return $html; }
	public function img($src, $opts = array()) { $html = self::tag_open('img', $opts + array('src' => $src)); $this->store($html, $opts); return $html; }

	public function gap($opts = array()) { $html = '<br>'; $this->store($html, $opts); return $html; }

	/* icon helper */
	public function icon($name, $opts = array()) { $html = self::tag('i', '', array('class' => 'icon-'.$name)); $this->store($html, $opts); return $html; }

	/* components */
	public function thumbnail($opts = array(), $extra_opts = array()) {
		if (is_string($opts)) { $extra_opts['image'] = $opts; $opts = $extra_opts; }
		$opts = self::apply_defaults($opts, array(
			'image' => '',
			'name' => 'Unknown',
			'description' => '',
			'size' => '100x100',
		));
		if (empty($opts->alt)) $opts->alt = $opts->name." thumbnail";

		// TODO : validate size; break into w/h and set as img attrs

		$html = self::tag_open('div', array('class' => 'thumbnail'));
		$html .= $this->img('', array_merge(
			(empty($opts->image) ? array("data-src" => "holder.js/{$opts->size}/text:{$opts->alt}") : array("src" => $opts->image)),
			array('alt' => $opts->alt, 'return_only' => true)
		));
		if (empty($template->name)) $html .= $this->h4($opts->name, array('return_only' => true));
		if (empty($template->description)) $html .= $this->p($opts->description, array('return_only' => true));
		$html .= '</div>';

		$this->store($html, $opts);
		return $html;
	}
	public function thumbnails($thumbs, $opts = array()) {
		// TODO : thumbs must be (non-empty?) array
		$thumb_ids = array_keys($thumbs);

		$opts = self::apply_defaults($opts, array(
			'name' => 'thumbs', // controlling hidden input
			'active' => reset($thumb_ids), 
			'size' => '100x100',
		));

		// TODO : convert to generic validation call
		if (!in_array($opts->active, $thumb_ids)) $opts->active = reset($thumb_ids);

		$html = self::tag_open('ul', array('class' => 'thumbnails clearfix'));
		$html .= $this->hidden($opts->name, $opts->active, array('return_only' => true));
		foreach ($thumbs as $thumb_id => $thumb) {
			$html .= self::tag_open('li', array(
				'class' => 'span3 selectable'.($thumb_id == $opts->active ? ' selected' : '').(empty($thumb->disabled) ? '' : ' muted'),
				'data-value' => $thumb_id,
				'data-input' => self::id_for_name($opts->name),
			));
			$html .= $this->thumbnail((array)$thumb + array('size' => $opts->size, 'alt' => $thumb->name, 'return_only' => true));
			$html .= "</li>";
		}
		$html .= '</ul>';

		$this->store($html, $opts);
		return $html;
	}

	public function tabs_open($tabs, $opts = array()) {
		$tab_ids = array_keys($tabs);
		if (!empty($tabs_ids)) array_walk($tabs, function(&$v,&$k) { $k = 'tab_'.$k; });

		$opts = self::apply_defaults($opts, array(
			'direction' => 'above',
			'active' => reset($tab_ids), // first id -- TODO : somehow store active tab?
		));

		array_push($this->current_active_tab, $opts->active);

		// TODO : convert to generic validation call
		if (!in_array($opts->direction, array('','above','left','right','below'))) $opts->direction = 'above';

		$html = "<div class='tabbable".($opts->direction == 'above' || $opts->direction == '' ? '' : 'tabs-'.$opts->direction)."'>";
		$html .= self::tag_open('ul', array('class' => 'nav nav-tabs'));
		foreach ($tabs as $key => $title) {
			$html .= self::tag('li', self::tag('a', $title, array('href' => '#tab_'.$key, 'data-toggle' => 'tab')), $key == $opts->active ? array('class' => 'active') : array());
		}
		$html .= "</ul>";
		$html .= self::tag_open('div', array('class' => 'tab-content'));

		$this->store($html); 
		return $html; 
	}
	public function tabs_close() { array_pop($this->current_active_tab); $html = '</div></div>'; $this->store($html); return $html; }

	public function tab_open($key, $opts = array()) {
		$html = "<div class='tab-pane".($key == reset($this->current_active_tab) ? ' active' : '')."' id='tab_{$key}'>";
		$this->store($html); 
		return $html; 
	}
	public function tab_close() { $html = '</div>'; $this->store($html); return $html; }


	/* forms */
	public function form_open($opts = array()) {
		$opts = self::apply_defaults($opts, array(
			'tag' => 'form',
			'action' => '', // self
			'method' => 'post',
			'type' => 'vertical',
		));
		// TODO : convert to generic validation call
		if (!in_array($opts->type, array('vertical','horizontal','inline','search'))) $opts->type = '';
		// TODO : convert to array_push?
		$this->current_form_tag = $opts->tag;

		$attrs = array_merge(
			empty($opts->type) ? array() : array('class' => 'form-'.$opts->type),
			$opts->tag == 'form' ? array('action' => $opts->action, 'method' => $opts->method) : array()
		);
		$html = self::tag_open($opts->tag, $attrs); 
		$this->store($html, $opts);
		return $html;
	}
	public function form_close($tag = '') {
		if (trim($tag) == '') $tag = $this->current_form_tag == '' ? 'form' : $this->current_form_tag;
		$html = '</'.$tag.'>';
		$this->store($html);
		return $html;
	}

	/* form inputs */
	public function bool($opts = array()) { return $this->checkbox($opts); }
	public function boolean($opts = array()) { return $this->checkbox($opts); }
	public function checkbox($opts = array()) {
		$opts = self::apply_defaults($opts, array(
            'disabled' => false,
            'checked' => false,
            'label' => '',
            'value' => 1,
            'inline' => false,
            'explain' => '',
        ));
        if (!empty($opts->name) && empty($opts->id)) $opts->id = self::id_for_name($opts->name, $opts->value);
		$attrs = array_merge(
			array('type' => "checkbox", 'value' => $opts->value),
			empty($opts->checked)  ? array() : array('checked' => 'checked'),
			empty($opts->name)     ? array() : array('name' => $opts->name, 'id' => $opts->id),
			empty($opts->disabled) ? array() : array('disabled' => 'disabled')
		);

		$has_label = trim($opts->label) != '';

		$html = $has_label ? "<label class='checkbox".($opts->disabled ? " muted" : "").($opts->inline ? " inline" : "")."'>" : "";
		$html .= self::tag_open("input", $attrs);
		$html .= $has_label ? "\n{$opts->label}</label>" : "";
		$html .= self::explain($opts->explain);

		$this->store($html, $opts);
		return $html;
	}

	// Michael's "fool-proof Boolean" method
	public function radio_bool($name, $selected, $opts = array()) {
		return $this->radio_list($name, $selected, array(array('value' => '1', 'label' => 'Yes'), array('value' => '0', 'label' => 'No')), $opts);
	}

	public function radio_list($name, $selected, $radios = array(), $opts = array()) {
		$opts = self::apply_defaults($opts, array(
            'disabled' => false,
            'inline' => false,
            'explain' => '',
        ));

		$html = '';
		foreach ($radios as $radio_opts) {
			$radio_opts = self::apply_defaults($radio_opts, array(
				'return_only' => true, // leave store() call to radio_list()
				'checked' => $selected == (is_array($radio_opts) ? $radio_opts['value'] : $radio_opts->value),
				'inline' => $opts->inline,
	            'disabled' => $opts->disabled,
	            'name' => $name,
			));

			$html .= $this->radio($radio_opts);
		}
		$html .= self::explain($opts->explain);

		$this->store($html, $opts);
		return $html;
	}

	public function radio($opts = array()) {
		$opts = self::apply_defaults($opts, array(
            'disabled' => false,
            'checked' => false,
            'label' => '',
            'value' => 1,
            'inline' => false,
            'explain' => '',
        ));
        if (!empty($opts->name) && empty($opts->id)) $opts->id = self::id_for_name($opts->name, $opts->value);
		$attrs = array_merge(
			array('type' => "radio", 'value' => $opts->value),
			empty($opts->checked)  ? array() : array('checked' => 'checked'),
			empty($opts->name)     ? array() : array('name' => $opts->name, 'id' => $opts->id),
			empty($opts->disabled) ? array() : array('disabled' => 'disabled')
		);

		$has_label = trim($opts->label) != '';

		$html = $has_label ? "<label class='radio".($opts->disabled ? " muted" : "").($opts->inline ? " inline" : "")."'>" : "";
		$html .= self::tag_open("input", $attrs);
		$html .= $has_label ? "\n{$opts->label}</label>" : "";
		$html .= self::explain($opts->explain);

		$this->store($html, $opts);
		return $html;
	}

	public function textbox($opts = array()) { return $this->text($opts); }
	public function text($opts = array()) {
		$opts = self::apply_defaults($opts, array(
            'disabled' => false,
            'label' => '',
            'value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
        ));
        if (!empty($opts->name) && empty($opts->id)) $opts->id = self::id_for_name($opts->name);
		$attrs = array_merge(
			array('type' => "text", 'value' => $opts->value, 'placeholder' => $opts->placeholder),
			empty($opts->class)    ? array() : array('class' => $opts->class),
			empty($opts->name)     ? array() : array('name' => $opts->name, 'id' => $opts->id),
			empty($opts->disabled) ? array() : array('disabled' => 'disabled')
		);

		$has_label = trim($opts->label) != '';
		$has_prepend = trim($opts->prepend) != '';
		$has_append = trim($opts->append) != '';

		$html = $has_label ? self::tag('label', $opts->label, array('for' => $opts->id) + ($opts->disabled ? array('class' => 'muted') : array()))."\n" : "";
		if ($has_prepend || $has_append) $html .= self::tag_open('div', array('class' => trim(($has_prepend ? 'input-prepend' : '').' '.($has_append ? 'input-append' : ''))));
		if ($has_prepend) $html .= self::tag('span', $opts->prepend, array('class' => 'add-on'));
		$html .= self::tag_open("input", $attrs);
		if ($has_append) $html .= self::tag('span', $opts->append, array('class' => 'add-on'));
		if ($has_prepend || $has_append)  $html .= "</div>";

		$this->store($html, $opts);
		return $html;
	}

	public function dropdown($opts = array()) { return $this->select($opts); }
	public function pulldown($opts = array()) { return $this->select($opts); }
	public function select($opts = array()) {
		$opts = self::apply_defaults($opts, array(
            'disabled' => false,
            'label' => '',
            'value' => '',
            'options' => array(),
            'none_option' => false,
        ));
        if (!empty($opts->name) && empty($opts->id)) $opts->id = self::id_for_name($opts->name);
		$attrs = array_merge(
			empty($opts->class)    ? array() : array('class' => $opts->class),
			empty($opts->name)     ? array() : array('name' => $opts->name, 'id' => $opts->id),
			empty($opts->disabled) ? array() : array('disabled' => 'disabled')
		);

		$has_label = trim($opts->label) != '';

		$html = $has_label ? "<label".($opts->disabled ? " class='muted'" : "")." for='{$opts->id}'>{$opts->label}</label>\n" : "";
		$options = array();
		if (!empty($opts->none_option)) array_unshift($opts->options, "None"); //$opts->options = array('0' => 'None') + $opts->options;
		if (!empty($opts->options)) foreach ($opts->options as $value => $label) {
			$option_attrs = array();
			if (is_array($label)) { $option_attrs = $label; $label = $label['label']; unset($option_attrs['label']); }
			// stringification is to allow '0' and other numeric values to be passed as value params
			$options[] = self::tag('option', $label, array('value' => "$value") + ("$value" == "{$opts->value}" ? array('selected' => 'selected') : array()) + $option_attrs);
		}
		$html .= self::tag("select", join($options), $attrs);

		$this->store($html, $opts);
		return $html;
	}

	public function hidden($name, $value, $opts = array()) {
		$attrs = array('type' => "hidden", 'id' => self::id_for_name($name), 'name' => $name, 'value' => $value) + $opts;

		$html = self::tag_open("input", $attrs);

		$this->store($html, $opts);
		return $html;
	}

	public function submit($opts = array()) { return $this->button(array('type' => 'submit', 'label' => 'Submit', 'name' => 'submit', 'value' => 'Submit') + $opts); }
	public function button($opts = array()) {
		$opts = self::apply_defaults($opts, array(
			'disabled' => false,
			'label' => '',
			'value' => '',
			'prepend' => '',
			'append' => '',
		));
		if (!empty($opts->name) && empty($opts->id)) $opts->id = self::id_for_name($opts->name);
		$attrs = array_merge(
			array('value' => $opts->value),
			empty($opts->type)     ? array('type' => 'button') : array('type' => $opts->type),
			empty($opts->class)    ? array('class' => 'btn') : array('class' => 'btn '.$opts->class),
			empty($opts->name)     ? array() : array('name' => $opts->name, 'id' => $opts->id),
			empty($opts->disabled) ? array() : array('disabled' => 'disabled')
		);

		$html = self::tag_open("input", $attrs);

		$this->store($html, $opts);
		return $html;
	}
}

?>