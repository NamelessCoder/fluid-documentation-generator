.. include:: ../../../../../Includes.txt

==================================
moduleLayout.button.shortcutButton
==================================


A view helper for adding a shortcut button to the doc header area.
It must be a child of <be:moduleLayout>
= Examples =
<code>
<be:moduleLayout>
     <be:moduleLayout.button.shortcutButton displayName="Shortcut label" />
</be:moduleLayout>
</code>

Arguments
=========


icon (string)
-------------


Icon identifier for the button

title (string)
--------------


Title of the button

disabled (anySimpleType)
------------------------


Default: false

Whether the button is disabled

showLabel (anySimpleType)
-------------------------


Default: false

Defines whether to show the title as a label within the button

position (string)
-----------------


Position of the button (left or right)

group (integer)
---------------


Button group of the button

displayName (string)
--------------------


Name for the shortcut

getVars (anySimpleType)
-----------------------


Default: array ()

List of additional GET variables to store. The current id, module and all module arguments will always be stored