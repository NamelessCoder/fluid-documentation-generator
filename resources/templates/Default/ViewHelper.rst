.. include:: {rootPath}Includes.txt

========================
ViewHelper Documentation {f:if(condition: metadata.namespace.alias, then: '{metadata.namespace.alias}:')}{viewHelper.localName}
========================

<f:if condition="{viewHelper.description}">
    <f:then>
        {viewHelper.description -> f:format.raw()}
    </f:then>
    <f:else>
        This ViewHelper has no description.
    </f:else>
</f:if>

Arguments
=========

<f:if condition="{viewHelper.argumentDefinitions -> f:count()} == 0">
    This ViewHelper has no arguments
</f:if>
<f:render partial="Arguments" arguments="{arguments: viewHelper.argumentDefinitions}" />
