.. include:: {rootPath}Includes.txt

{headlineDecoration}
{headline}
{headlineDecoration}


{viewHelper.description -> f:format.raw()}

Arguments
=========


<f:spaceless>
<f:if condition="{arguments -> f:count()} == 0">This ViewHelper has no arguments.</f:if>

<f:for each="{arguments}" as="argumentData">
{argumentData.headline}
{argumentData.headlineDecoration}

<f:if condition="{argumentData.default}">
{argumentData.default}</f:if>
{argumentData.description}
</f:for>
</f:spaceless>