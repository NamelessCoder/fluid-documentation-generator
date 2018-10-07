<f:if condition="{resources -> f:count()}">
Resources
---------
        <f:for each="{resources}" as="resourceUrl" key="resourceName">
* <a href="{resourceUrl}">
                    {resourceName}
                </a>
        </f:for>
</f:if>
