<?php

class LIFX {

	public $token;
	public $headers;

	public function __construct() {

		$this->token = "";
		$this->headers = array("Authorization: Bearer ".$this->token);

	}
	
	public function Exec($link, $data = "", $extra = "false") {

		$exec = curl_init($link);

		curl_setopt($exec, CURLOPT_HTTPHEADER, $this->headers);
		curl_setopt($exec, CURLOPT_RETURNTRANSFER, true);
		
		
		if ($extra == "true") {
			
			curl_setopt($exec, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($exec, CURLOPT_POSTFIELDS, $data);
			
		}
		
		elseif ($extra == "semi") {

			curl_setopt($exec, CURLOPT_CUSTOMREQUEST, "PUT");
			
		}

		$response = curl_exec($exec);
		
		curl_close($exec);

		return $response;

	}
	
	public function back() {
		echo "<script type=\"text/javascript\">window.location.replace(\"http://buzz.id.au/lifx/\");</script>";
	}
	
	public function power($opt = null) {
		
		if (is_null($opt)) { echo "No power option: needs ON or OFF parameter"; }
		else {
			
			$this->Exec("https://api.lifx.com/v1/lights/all/state", "power=".$opt, true);
			
		}
		
		$this->back();
		
	}
	
	public function getScenes() {
		
		$sceneDump = json_decode($this->Exec("https://api.lifx.com/v1/scenes"));
		$x = 0;
		$scenes = array();
		
		?>
		
		<form action="exec.php">
			<input type="hidden" name="a" value="setscene">
			<select name="id">
			
		<?php
		
		foreach ($sceneDump as $sceneType) {
			$scenes[$x]["name"] = $sceneType->name;
			$scenes[$x]["id"] = $sceneType->uuid;
			$x++;
			echo "<option value=\"".$sceneType->uuid."\">".$sceneType->name."</option>";
		}
		
		?>

			</select>
			<br><br>
			<input type="submit">
		</form>
		
		<?php
		
	}
	
	public function setScenes($id) {

		$link = "https://api.lifx.com/v1/scenes/scene_id:".$id."/activate";
		$sceneDump = json_decode($this->Exec($link, "", "semi"));
		$this->back();
		
	}
	
	public function listLights() {
		
		$link = "https://api.lifx.com/v1/lights/all";
		$lightDump = json_decode($this->Exec($link, ""));
		$x = 0;
		$lights = array();
		
		
		foreach ($lightDump as $lightInfo) {
			echo "
			<div class=\"light\" id=\"".$lightInfo->uuid."\">";
			if ($lightInfo->color->saturation == 0 && $lightInfo->color->kelvin != 0 && $lightInfo->power == "on") {
				echo "<div class=\"light-image\" style=\"background: ".$this->kelv2hex($lightInfo->color->kelvin)."; border-radius: 50px;\"><img id=\"".$lightInfo->power."\" src=\"http://buzz.id.au/lifx/img/bulb.png\" /></div>";
			}
			elseif ($lightInfo->color->saturation == 0) {
				echo "<div class=\"light-image\"><img id=\"".$lightInfo->power."\" src=\"http://buzz.id.au/lifx/img/bulb.png\" /></div>";
			}
			elseif ($lightInfo->power == "off") {
				echo "<div class=\"light-image\"><img id=\"".$lightInfo->power."\" src=\"http://buzz.id.au/lifx/img/bulb.png\" /></div>";
			}
			else {
				$rgb = $this->testRGB($lightInfo->color->hue);
				echo "<div class=\"light-image\" style=\"background: rgb(".$rgb['r'].", ".$rgb['g'].", ".$rgb['b']."); border-radius: 50px;\"><img id=\"".$lightInfo->power."\" src=\"http://buzz.id.au/lifx/img/bulb.png\" /></div>";
			}
			echo"	<div class=\"light-name\">".$lightInfo->label."</div>
			</div>
			";	
		}
		
	}
	
	public function debug() {
		
		$link = "https://api.lifx.com/v1/lights/all";
		$lightDump = json_decode($this->Exec($link, ""));
		print_r($lightDump);
		$sceneDump = json_decode($this->Exec("https://api.lifx.com/v1/scenes"));
		print_r($sceneDump);
		foreach ($sceneDump as $sceneInfo) {
			if (count($sceneInfo->states) > 1) { 
				foreach($sceneInfo as $sceneStates) {
					print_r($sceneStates);
				}
			}
		}
		
	}
	
	public function kelv2hex($k) {
		switch($k) {
			case '2500K':
				return '#FFDEB8';
				break;
			case '2750K':
				return '#FFE1B8';
				break;
			case '3000K':
				return '#FFE4C2';
				break;
			case '3200K':
				return '#FEE5C6';
				break;
			case '3500K':
				return '#FDE5C9';
				break;
			case '4000K':
				return '#FFEBD2';
				break;
			case '4500K':
				return '#FFEFD9';
				break;
			case '5000K':
				return '#FEF0DC';
				break;
			case '5500K':
				return '#FDF0E1';
				break;
			case '6000K':
				return '#F9F1E6';
				break;
			case '6500K':
				return '#F6F2EB';
				break;
			case '7000K':
				return '#F2F0ED';
				break;
			case '7500K':
				return '#ECEDEE';
				break;
			case '8000K':
				return '#EDF1F6';
				break;
			case '8500K':
				return '#EBF2F9';
				break;
			case '9000K':
				return '#EBF2F9';
				break;
		}
	}
	
	public function testRGB($hue) { 

		$hue = $hue / 360;
		return $this->hsl($hue);
	
	}
	
	public function hsl($hue, $sat = 1, $light = 0.5) {
		
		if ($sat == 0) {
				$r = $g = $b = $light * 255;
		}
		else {
			if ($light < 0.5) { 
				$hsl2 = $light * (1 + $sat); 
			}
			else {
				$hsl2 = ($light + $sat) - ($sat * $light); 
			}
			$hsl1 = 2 * $light - $hsl2;
			$r = 255 * $this->h2rgb($hsl1, $hsl2, $hue + (1 / 3));
			$g = 255 * $this->h2rgb($hsl1, $hsl2, $hue);
			$b = 255 * $this->h2rgb($hsl1, $hsl2, $hue - (1 / 3));
		}
		
		$rgb = array("r" => round($r),"g" => round($g),"b" => round($b));
		return $rgb;
		
	}
	
	public function h2rgb($hsl1, $hsl2, $hue) {
		
		if ($hue < 0) { $hue += 1; }
		if ($hue > 1) { $hue -= 1; }
		if ((6 * $hue) < 1) { return ($hsl1 + ($hsl2 - $hsl1) * 6 * $hue); }
		if ((2 * $hue) < 1) { return $hsl2; }
		if ((3 * $hue) < 2) { return ($hsl1 + ($hsl2 - $hsl1) * ((2 / 3) - $hue) * 6); }
		return $hsl1;
		
	}

}

?>
