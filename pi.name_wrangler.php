<?php

$plugin_info = array(
	'pi_name' => 'Name Wrangler',
	'pi_version' => '1.0.4',
	'pi_author' => 'Derek Hogue',
	'pi_author_url' => 'http://github.com/amphibian/pi.name_wrangler.ee_addon',
	'pi_description' => 'Store proper names in their ideal format but still use them in all sorts of namey ways.',
	'pi_usage' => Name_wrangler::usage()
);

class Name_wrangler
{

	var $return_data = "";

	function Name_wrangler($str = '')
	{
		global $TMPL;
		
		if($str == '') $str = $TMPL->tagdata;

		$type = ( $TMPL->fetch_param('type') == '' ) ? 'full' : $TMPL->fetch_param('type');
		$form = ( $TMPL->fetch_param('form') == '' ) ? 'singular' : $TMPL->fetch_param('form');
		$multiple = ( $TMPL->fetch_param('multiple') == 'off' ) ? FALSE : TRUE;
		
		// Replace all forms of ampersand with a standard delimiter
		$str = ($multiple == TRUE) ? str_replace(array(' & ', '&amp;', '&#38;'), '+', $str) : $str;		
		
		$names = array();
		
		// Check if we have multiple names
		if( $people = explode('+', $str) )
		{
			foreach($people as $person)
			{
				// Transpose each name
				$names[] = $this->transpose($person);
			}
		}
		else
		{
			// Transpose the one name
			$names[] = $this->transpose($str);
		}
		
		if($type != 'full')
		{	
			// We're returning only part of each name
			$name_segments = array();
			foreach($names as $name)
			{
				$name_parts = explode(' ', $name);
				
				// If there are two words or more in the name, the last one is the surname
				$surname = array_pop($name_parts);
				
				// If there remains more than one word, the first one is the first name
				$firstname = array_shift($name_parts);
				
				// If there's anything left in the array, it's all middle names
				$middlename = (!empty($name_parts)) ? implode(' ', $name_parts) : '';
				
				switch($type)
				{
					case 'first': $name_segments[] = $firstname;
					break;
					
					case 'middle': $name_segments[] = $middlename;
					break;

					case 'first+middle': $name_segments[] = $firstname . ' ' . $middlename;
					break;
					
					case 'last': $name_segments[] = $surname;
					break;					
				}
			}
			$r = $this->concatenate_names($name_segments);
		}
		else
		{
			$r = $this->concatenate_names($names);
		}
		
		$this->return_data = ($form == 'posessive') ? $this->posessive($r) : $r;
	}
	

	// Accepts the string $name
	// Transposes first and last names in a string based on a comma separator
	function transpose($name)
	{
		return trim(implode(' ', array_reverse(explode(',', trim($name)))));
	}


	// Accepts the string $name
	// Smart-posessiveness added to last word in the string
	function posessive($name)
	{
		return (substr(strtolower($name), -1) == 's') ? $name . '&rsquo;' : $name . '&rsquo;s';
	}
	

	// Accepts array $names
	// Compiles an array of strings into a comma-separated list,
	// with a custom separator $and before the last item in the array	
	function concatenate_names($names)
	{
		global $TMPL;
		$and = ( $TMPL->fetch_param('and') == '' ) ? ' &amp; ' : $TMPL->fetch_param('and');
		
		if(count($names) > 2)
		{
			$final_name = array_pop($names);
			$r = trim(implode(', ', $names)) . ' ' . $and . ' ' . $final_name;
		}
		else
		{
			$r = trim(implode(' ' . $and . ' ', $names));
		}
		return $r;
	}


	function usage()
	{
	ob_start(); 
	?>
Name Wrangler lets you use proper names in the format SURNAME, GIVEN NAME in the back-end (in order to faciliate proper sorting), but still display them in the the format GIVEN NAME SURNAME. It also allows you to display first, middle, and last names independently, in both singular and posessive forms, and to do this with both single names and lists of ampersand- or plus-separated names.

Example use:

{exp:name_wrangler}Hedges, Chris{/exp:name_wrangler} consistently impresses me with his writing and journalism.

Returns "Chris Hedges consistently impresses me with his writing and journalism."

{exp:name_wrangler type="first" form="posessive"}Hedges, Chris{/exp:name_wrangler} latest book is a sobering read.

This returns "Chris' latest book is a sobering read."

{exp:name_wrangler type="last"}Hedges, Chris + Moore, Alan + Robinson, Kim Stanley{/exp:name_wrangler} are very different, but all must-read authors.

This returns "Hedges, Moore & Robinson are very different, but all must-read authors."

Parameters:

"type": the type of name(s) to return. Either "full", "first", "middle", "first+middle" or "last".  Defaults to "full".
"form": either "singular" or "posessive". Defaults to "singular".
"multiple": if set to "off", Name Wrangler will not look for multiple names (useful for when you want to use ampersands within names).
"and": word or character entity to use  before the last name at the end of a list of names.  Defaults to "&amp;".

	<?php
	$buffer = ob_get_contents();
	
	ob_end_clean(); 
	
	return $buffer;
	}

}

?>