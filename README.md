# Project Name: Document Manager

## Project status: Completed

![Module-logo](https://github.com/OliviaA22/document-manager/assets/94966149/326f5ed8-1624-4b59-b2ba-5a1daebd5b9d)

## Description

This HumHub module Document Management, offers comprehensive functionality for managing documents efficiently within your HumHub instance. With a user-friendly interface and robust features, this module simplifies document organization and access for both users and administrators.
It empowers users and administrators with seamless document management capabilities, offering user-friendly interfaces and advanced features tailored to various needs.

## Key Functionalities

- Efficient redirection to respective ("revision/create" or "document/create") forms.
- Simplified revision creation using the "+" icon next to documents, enhancing user-friendliness.
- Simultaneous folder and subfolder creation within the document creation process, boosting efficiency.
- Streamlined document creation so that Admins can effortlessly create, read, update, and delete documents and folders.
- Granular control over document visibility using the "is_visible" column.
- Intuitive Admin Interface: Admins can view all documents, while user visibility adheres to "is_visible" settings.
- Optimized document name display for clarity, utilizing the file name.
- Organized treeview presentation of folders and documents, ensuring intuitive navigation.
- Quickly locate specific documents using relevant keywords, saving time and effort.
- Navigation Breadcrumbs, easy-to-follow and provide clear context and enhance user experience.
- User-Centric Design

### User Interface

In the user view, users can seamlessly navigate through folders and documents, thanks to our intuitive treeview display format. Clicking on a folder expands its contents, revealing documents and subfolders contained within. Users can effortlessly download documents by simply clicking on them. Additionally, we've implemented a modified document name display pattern, using the file name for improved clarity and ease of identification.

### Admin Interface

Administrators have access to a powerful set of tools for document management. CRUD operations are fully supported, allowing administrators to create, read, update, and delete documents and folders with ease. The addition of a prompt when clicking the "Add" button streamlines the process of creating new documents and revisions. Furthermore, the creation of revisions is more intuitive, with the option conveniently located beside each document for quick access.

### Document Visibility

Document visibility is intelligently managed through the "is_visible" column. When set to 'No', documents remain hidden from regular users while remaining accessible to administrators. This feature ensures that sensitive or work-in-progress documents are protected from unauthorized access.

### Search Functionality and Breadcrumbs

Finding specific documents is made effortless with our integrated search functionality. Users can quickly locate documents by entering relevant keywords, streamlining the document retrieval process. Additionally, breadcrumbs provide users with easy navigation, allowing them to track their location within the folder structure and backtrack as needed.

### Notification Feature

Our module includes a robust notification system, powered by a scheduled CronJob. Notifications are queued and sent out at specified intervals, ensuring timely communication with users. For urgent matters, administrators have the option to trigger instant notifications, ensuring critical updates are promptly delivered.

## Technical Specifications

- HumHub Module (Integration details based on HumHub version)
- Programming Languages (PHP, JavaScript, JQuery, etc.)
- Framework (YII2)
- Database Configuration (MySQL, MSSQL...)
