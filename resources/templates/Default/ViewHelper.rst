.. include:: {rootPath}Includes.txt

.. _{headlineIdentifier}:

{headlineDecoration}
{headline}
{headlineDecoration}


{viewHelper.description -> f:format.raw()}

Arguments
=========


<f:spaceless>
<f:if condition="{arguments -> f:count()} == 0">This ViewHelper has no arguments.</f:if>

<f:for each="{arguments}" as="argumentData">
.. _{argumentData.headlineIdentifier}:
{argumentData.headline}
{argumentData.headlineDecoration}
<f:if condition="{argumentData.dataType}">
:aspect:`DataType`
   {argumentData.dataType}
</f:if><f:if condition="{argumentData.default}">
:aspect:`Default`
   {argumentData.default -> f:format.raw()}
</f:if>
:aspect:`Required`
   {argumentData.isRequired ? 'true' : 'false'}
:aspect:`Description`
   {argumentData.description -> f:format.raw()}
</f:for>
</f:spaceless>
