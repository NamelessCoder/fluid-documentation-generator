.. include:: ../../../Includes.txt

============
moduleLayout
============


A view helper for having properly styled backend modules.
It is recommended to use it in Fluid Layouts.
It will render the required HTML for the doc header.
All module specific output and further configuration of the doc header
must be rendered as children of this view helper.
= Examples =
<code>
<be:moduleLayout>
    <f:render section="content" />
</be:moduleLayout>
</code>
<output>
<!-- HTML of the backend module -->
</output>

Arguments
=========


This ViewHelper has no arguments.