.. include:: {rootPath}Includes.txt

{headlineDecoration}
{headline}
{headlineDecoration}


{viewHelper.description -> f:format.raw()}

Arguments
=========

<f:if condition="{viewHelper.argumentDefinitions -> f:count()} == 0">
    This ViewHelper has no arguments
</f:if>
<f:render partial="Arguments" arguments="{arguments: viewHelper.argumentDefinitions}" />
