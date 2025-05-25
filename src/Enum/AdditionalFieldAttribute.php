<?php

declare(strict_types=1);

namespace MageHx\MahxCheckout\Enum;

enum AdditionalFieldAttribute: string
{
    case ID = 'id';
    case OPTIONS = 'options';
    case DEFAULT_OPTION_LABEL = 'defaultOptionLabel';
    case INPUT_EXTRA_ATTRIBUTES = 'inputElemAdditionalAttributes';
    case BEFORE_INPUT_HTML = 'beforeInputHtml';
    case AFTER_INPUT_HTML = 'afterInputHtml';
    case WRAPPER_ELEM_EXTRA_CLASS = 'wrapperElemExtraClass';
    case WRAPPER_ELEM_EXTRA_ATTRIBUTES = 'wrapperElemAdditionalAttributes';
    case MULTILINE_COUNT = 'multilineCount';
}
