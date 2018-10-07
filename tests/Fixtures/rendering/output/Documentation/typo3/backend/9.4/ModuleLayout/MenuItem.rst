.. include:: ../../../../Includes.txt

=====================
moduleLayout.menuItem
=====================


A view helper for adding a menu item to a doc header menu.
It must be a child of <be:moduleLayout.menu>
= Examples =
<code>
<be:moduleLayout>
    <be:moduleLayout.menu identifier="MenuIdentifier">
         <be:moduleLayout.menuItem label="Menu item 1" uri="{f:uri.action(action: 'index')}"/>
    </be:moduleLayout.menu>
</be:moduleLayout>
</code>

Arguments
=========


label (string)
--------------


Label of the menu item

uri (string)
------------


Action uri