<?php

/*
* PHOTO Simple Gallery
* Copyright (C) 2015 Rosario Prestigiacomo
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

class Pagination {

    public $current_page;
    public $per_page;
    public $total_count;

    public function __construct($page=1, $per_page=20, $total_count=0) {
        $this->current_page = (int)$page;
        $this->per_page = (int)$per_page;
        $this->total_count = (int)$total_count;
    }

    public function offset() {
        return ($this->current_page -1) * $this->per_page;
    }

    public function total_pages() {
        return ceil($this->total_count/$this->per_page);
    }

    public function previous_page() {
        return $this->current_page - 1;
    }

    public function next_page() {
        return $this->current_page + 1;
    }
    
    public function penultimate_page() {
		return $this->total_pages() - 1;
	}

    public function has_previous_page() {
        return $this->previous_page() >= 1 ? true : false;
    }

    public function has_next_page() {
        return $this->next_page() <= $this->total_pages() ? true : false;
    }
    
    public function display($getdata="", $adjacents=3) { 
		$html = "";
		
		if(!empty($getdata)) { $getdata .= "&amp;"; }

		if($this->total_pages() > 1) { 
			if ($this->has_previous_page()) {
				$html .= "<a href=\"?{$getdata}page={$this->previous_page()}\">&laquo;</a>";
			}
			if ($this->total_pages() < 7 + ($adjacents * 2)) {
				for ($counter = 1; $counter <= $this->total_pages(); $counter++) {
					if ($counter == $this->current_page) {
						$html .= "<span class=\"selected\">{$counter}</span>";
					} else {
						$html .= "<a href=\"?{$getdata}page={$counter}\">{$counter}</a>";
					} 
				}
			} elseif($this->total_pages() > 5 + ($adjacents * 2)) {
				if($this->current_page < 3 + ($adjacents * 2)) {
					for ($counter = 1; $counter < 4 + ($adjacents * 2); $counter++) {
						if ($counter == $this->current_page) {
							$html .= "<span class=\"selected\">{$counter}</span>";
						} else {
							$html .= "<a href=\"?{$getdata}page={$counter}\">{$counter}</a>";
						} 
					}
				$html .= "...";			 
				$html .= "<a href=\"?{$getdata}page={$this->penultimate_page()}\">{$this->penultimate_page()}</a>";
				$html .= "<a href=\"?{$getdata}page={$this->total_pages()}\">{$this->total_pages()}</a>";		   
				} elseif($this->total_pages() - ($adjacents * 2) > $this->current_page && $this->current_page > ($adjacents * 2)) { 
					$html .= "<a href=\"?{$getdata}page=1\">1</a><a href=\"?page=2\">2</a>";					
					$html .= "...";					  
					for ($counter = $this->current_page - $adjacents; $counter <= $this->current_page + $adjacents; $counter++) { 
						if ($counter == $this->current_page) {
							$html .= "<span class=\"selected\">{$counter}</span>";
						} else {
						$html .= "<a href=\"?{$getdata}page={$counter}\">{$counter}</a>";
						} 
					} 
				$html .= "...";
				$html .= "<a href=\"?{$getdata}page={$this->penultimate_page()}\">{$this->penultimate_page()}</a>";
				$html .= "<a href=\"?{$getdata}page={$this->total_pages()}\">{$this->total_pages()}</a>";
				} else {
					$html .= "<a href=\"?{$getdata}page=1\">1</a><a href=\"?page=2\">2</a>";
					$html .= "...";
					for ($counter = $this->total_pages() - (2 + ($adjacents * 2)); $counter <= $this->total_pages(); $counter++) { 
						if ($counter == $this->current_page) {
							$html .= "<span class=\"selected\">{$counter}</span>";
						} else {
							$html .= "<a href=\"?{$getdata}page={$counter}\">{$counter}</a>";
						} 
					} 
				} 
			} 
			if ($this->has_next_page()) {
				$html.= "<a href=\"?{$getdata}page={$this->next_page()}\">&raquo;</a>";
			}
		}
		return $html;
	}
}

?>
