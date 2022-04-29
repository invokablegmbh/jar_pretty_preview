.. every .rst file should include Includes.txt
.. use correct path!

.. include:: /Includes.rst.txt

.. Every manual should have a start label for cross-referencing to
.. start page. Do not remove this!

.. _start:

=============================================================
Pretty Preview
=============================================================

:Version:
   1.0

:Language:
   en

:Description:
   Generates an automatic pretty preview of content elements in the backend based on the TCA fields.

:Authors:
   Julian Lichte, Maurice Möllmer

:Email:
   info@invokable.gmbh

:License:
   `Creative Commons BY 4.0 <https://creativecommons.org/licenses/by/4.0/>`__ 


What does it do?
================

*  When building individual content elements, a nice backend preview often falls by the wayside (due to budget or time). The editors have to deal with this, as they are overwhelmed by gray boxes with one-liners in the backend.
   
*  With this extension you can generate a nice preview of (almost) any content element within a very short time.

*  How does it works?

   *  Pretty Preview loads the underlaying data and TCA structure and generates a preview based on the visible and filled fields for the current user.

Example
=======

Before:

.. image:: /Images/before.png
   :alt: Before using Pretty Preview

|

After:

.. image:: /Images/after.png
   :alt: After using Pretty Preview

Installation
============

**Composer**

You can install `jar_pretty_preview` with following shell command:

.. code-block:: bash

   composer req jar/jar_pretty_preview


**Extensionmanager**

If you want to install `jar_pretty_preview` traditionally with Extensionmanager, follow these steps:

#. Visit ExtensionManager

#. Switch over to `Get Extensions`

#. Search for `jar_pretty_preview`

#. Install extension

---------------------------------------------------------------------------------

**TYPO3**

The content of this document is related to TYPO3 CMS,
a GNU/GPL CMS/Framework available from `typo3.org <https://typo3.org/>`_ .

**Extension Manual**

This documentation is for the TYPO3 extension Pretty Preview.
If you find an error or something is missing, please:
`Report a Problem <https://github.com/invokablegmbh/jar_pretty_preview/issues/new>`__


.. toctree::
   :maxdepth: 1

   Sitemap
   genindex


Author
------

This extension has been created by `JAR Media <https://jar.media>`__, a brand of `invokable <https://invokable.gmbh>`__.

.. image:: /Images/jarmedia_logo.svg
   :alt: JAR Media - be creative. and relax
   :target: https://jar.media/
   :width: 300

.. image:: /Images/spacerblock_40x40.png   

.. image:: /Images/invokable_logo.svg
   :alt: invokable GmbH
   :target: https://invokable.gmbh/
   :width: 300