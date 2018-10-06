.. include:: {rootPath}Includes.txt

{headlineDecoration}
{headline}
{headlineDecoration}


<f:spaceless>
{viewHelpers} ViewHelpers documented
<f:if condition="{subGroups} > 0">{subGroups} Sub namespaces</f:if>
</f:spaceless>

.. toctree::
   :titlesonly:
   :glob:

<f:for each="{tocTree}" as="line">{line}</f:for>


