<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2016, Phoronix Media
	Copyright (C) 2009 - 2016, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class hdd_read_speed extends phodevi_sensor
{
	const SENSOR_TYPE = 'hdd';
	const SENSOR_SENSES = 'read-speed';
	const SENSOR_UNIT = 'MB/s';
	const INSTANT_MEASUREMENT = false;

	private $disk_to_monitor = NULL;

	function __construct($instance, $parameter)
	{
		parent::__construct($instance, $parameter);

		if($parameter !== NULL)
		{
			$this->disk_to_monitor = $parameter;
		}
		else if(self::get_supported_devices() != null)
		{
			$disks = self::get_supported_devices();
			$this->disk_to_monitor = $disks[0];
		}
	}
	public static function parameter_check($parameter)
	{
		if($parameter === null || in_array($parameter, self::get_supported_devices() ) )
		{
			return true;
		}

		return false;
	}
	public function get_readable_device_name()
	{
		return $this->disk_to_monitor;
	}
	public static function get_supported_devices()
	{
		if(phodevi::is_linux())
		{
			$disk_list = shell_exec("ls -1 /sys/class/block | grep '^[shv]d[a-z]$'"); // TODO get rid of this way and use just PHP
			$disk_array = explode("\n", $disk_list);

			$supported = array();

			foreach($disk_array as $check_disk)
			{
				$stat_path = '/sys/class/block/' . $check_disk . '/stat';
				if(is_file($stat_path) && pts_file_io::file_get_contents($stat_path) != null)
				{
					$supported[] = $check_disk;
				}
			}

			return $supported;
		}

		return NULL;
	}
	public function read_sensor()
	{
		$read_speed = -1;

		if(phodevi::is_linux())
		{
			$read_speed = $this->hdd_read_speed_linux();
		}

		return pts_math::set_precision($read_speed, 2);
	}
	private function hdd_read_speed_linux()
	{
		if($this->disk_to_monitor == NULL)
		{
			return -1;
		}

		$stat_path = '/sys/class/block/' . $this->disk_to_monitor . '/stat';
		$speed = phodevi_linux_parser::read_sys_disk_speed($stat_path, 'READ');
		return $speed;
	}
}

?>
