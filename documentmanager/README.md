# Olivias Einarbeitung

## Project Name: Document Manager

## Project status: 90% Completed

### Completed Tasks

- [x] View for both users and admin has been created.
- [x] The user view only has the view and document download functionalities.
- [x] All CRUD operations are working for the Admin interface
- [x] Make document visibility dependent on the *'is_visible'* column.
      When it is 'No', the document is not displayed to the normal user but is visible to the Admin user.
- [x] MODIFY the document name display pattern (using the file name as the document name)
- [x] Display folders and documents in a treeview(recursive) format (with count (currently achieved with javascript)-not implemented yet).
- [x] moving the create new revision button to the action column.
- [x] Integrate into humhub and create a module.
- [x] MODIFY the search functions using the search fields
- [x] Create breadcrumbs for easy navigation
- [x] Create the notification feature using CRONjob (accumulative with the option for an instant notification trigger).
- [x] Optimize the functions that are reuseable

## Description

- In the Admin view, when the __Add__ button is clicked, a prompt is shown to create a new document. The new revision option redirects to the
  revision/create while the new document option redirects to the document/create.
  - The revision is created by clicking on the plus(+) icon beside each document. This is to modified to increase user friendliness and efficiency level.
  - Folders and subfolders can be created at the same time while creating a new document.

- In the normal user view, clicking on  a folder opens the documents and subfolders contained in it, while clicking on a document downloads the file.
- The user view is displayed in a hierarchical view.
- The notification feature is structured in a way that they are queued and then sent out using a scheduled CronJob.

## Roadmap (ToDo)

- [ ] Make most of the codes to be Yii-conformed
- [ ] Using tag buttons as a search input-clicking on the tags triggers a search for documents with the selected tag.
- [ ] A more Yii-conformed way of saving the affiliations when creating a new document

***

## Authors and acknowledgment
