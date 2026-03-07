<?php

namespace App\Enums;

enum SectionEnum: string
{
    const BG = 'bg_image';

    case EXAMPLE = 'example';
    case EXAMPLES = 'examples';

    case INTRO = 'intro';

    case ABOUT = 'about';
    case SUPPLYMENT = 'supplyment';
    case LIFE_WITHOUT = 'life_without';
    case WHAT_MAKES = 'what_makes';
    case EDUCATION_APPROACH = 'education_approach';
    case FOUNDER_STORY = 'founder_story';
    case WORK_TOGETHER = 'work_together';

    //common sections
    case FOOTER = 'footer';
    case HEADER = 'header';
    case BANNER  = 'BANNER';
    case HERO  = 'HERO';





}
