.. include:: ../../../../../Includes.txt

==============================
moduleLayout.button.linkButton
==============================


A view helper for adding a link button to the doc header area.
It must be a child of <be:moduleLayout>
= Examples =
<code>
<be:moduleLayout>
     <be:moduleLayout.button.linkButton
         icon="actions-add"
         title="Add record')}"
         link="{be:uri.newRecord(table: 'tx_my_table')}"
     />
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

link (string)
-------------


Link for the button