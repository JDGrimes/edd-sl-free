# EDD SL Free
This plugin integrates with the Software Licensing extension of the Easy Digital Downloads plugin, making it possible to provide free, unlicensed downloads in addition to licensed ones.

While it is already possible to provide free downloads through Easy Digital Downloads (EDD) even when using the Software Licensing (EDD SL) extension, it is necessary for the user to go through the checkout process and thus obtain a license, even for free downloads.

This plugin solves this issue by doing two things. First, it replaces the "Add to cart" button with a direct link to download the file. Secondly, it preempts the `get_version` API endpoint provided by EDD SL which is used to get a download's information, making this information available to API consumers without the user having to supply a license.

This allows you to offer free software in much the same way that plugins are free to download from WordPress.org—with absolutely no strings attached. This saves people the extra work of having to obtain and maintain an API key, while still providing the benefits of updates, etc.
