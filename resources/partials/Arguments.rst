<f:if condition="{arguments -> f:count()} > 5">
    <f:for each="{arguments}" as="argument">
* {argument.name}
</f:for>
</f:if>

<f:for each="{arguments}" as="argument">

{argument.name} ({argument.type}) {f:if(condition: '{argument.required} == 1', then: 'required')}
=================================

<f:if condition="{argument.default}">
Default: {argument.default}
</f:if>
{argument.description}
</f:for>
