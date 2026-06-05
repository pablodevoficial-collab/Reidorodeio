<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Widget extends Component
{
    public $style;
    public $link;
    public $icon;
    public $title;
    public $value;
    public $bg;
    public $type;
    public $heading;
    public $subheading;
    public $coverCursor;
    public $iconStyle;
    public $color;
    public $viewMoreIcon;

    /**
     * Create a new component instance.
     */
    public function __construct(
        $style = '6',
        $link = '#',
        $icon = '',
        $title = '',
        $value = '',
        $bg = 'primary',
        $type = '1',
        $heading = '',
        $subheading = '',
        $coverCursor = 0,
        $iconStyle = '',
        $color = 'primary',
        $viewMoreIcon = true
    ) {
        $this->style = $style;
        $this->link = $link;
        $this->icon = $icon;
        $this->title = $title;
        $this->value = $value;
        $this->bg = $bg;
        $this->type = $type;
        $this->heading = $heading;
        $this->subheading = $subheading;
        $this->coverCursor = $coverCursor;
        $this->iconStyle = $iconStyle;
        $this->color = $color;
        $this->viewMoreIcon = $viewMoreIcon;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('components.widget');
    }
}
