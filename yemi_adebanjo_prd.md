# Product Requirements Document (PRD)
## Yemi Adebanjo Personal Brand & Digital Commerce Platform

**Supporting brand:** Powered by Jolax Global Leadership Center  
**Document type:** Product Requirements Document (PRD)  
**Product category:** Personal branding, publishing, e-commerce, consultation booking, and content management  
**Proposed frontend:** Inertia.js + React + TypeScript (TSX)  
**Proposed backend:** Laravel  
**Database:** MySQL  
**Primary market:** Nigeria, with support for an international audience  
**Document status:** Draft for stakeholder review and approval

---

## 1. Executive Summary

The Yemi Adebanjo Personal Brand Platform will be a professional website that brings together the personal brand, publications, leadership teachings, ministry, consulting services, speaking engagements, and institutional activities of Yemi Adebanjo.

The website will operate as both a **personal brand platform and a digital business system**. Visitors will be able to learn about Yemi Adebanjo, explore his books, purchase digital and printed publications, book consultations, watch videos, browse photographs, read articles, submit speaking invitations, and subscribe to updates.

A secure administration dashboard will enable the owner and authorised team members to manage content, books, sales, appointments, events, enquiries, subscribers, and website settings without requiring a developer for routine updates.

The application will be developed with Laravel and Inertia.js using React and TypeScript (TSX), with MySQL as the primary database.

The requirements in this PRD are derived from the supplied website development brief.

### 1.1 Product Vision

To establish a central digital platform that communicates Yemi Adebanjo's personal and professional identity, makes his knowledge and publications accessible to a global audience, and provides reliable digital channels for commerce, consultation, speaking engagements, and professional interaction.

### 1.2 Product Objectives

The application must:

1. Establish a credible, professional, and internationally accessible online presence.
2. Present approved biographical information, professional activities, achievements, and institutional affiliations.
3. Provide a searchable catalogue of books and support their sale.
4. Deliver purchased digital publications securely.
5. Manage printed-book orders, stock, delivery, and order tracking information.
6. Allow visitors to schedule and pay for consultations.
7. Publish videos, articles, photographs, awards, testimonials, and events.
8. Provide a structured process for speaking invitations and general enquiries.
9. Build an opted-in email subscriber base.
10. Provide a secure administration dashboard for day-to-day operations.
11. Support search engine visibility, analytics, accessibility, performance, and data protection.
12. Give the owner control over content, customer interactions, and essential business accounts.

### 1.3 Key Success Principle

The application must be a functioning business platform, not merely a collection of attractive web pages.

A purchase button must initiate a real purchase process. A booking form must check actual availability. A download link must enforce access rules. Dashboard statistics must reflect actual database records.

Any feature dependent on an external provider, account approval, or missing content must be identified clearly and must not be presented as operational until it has been configured and tested.

---

## 2. Product Scope

### 2.1 In Scope

The initial product release will include the following modules.

| No. | Module | Primary purpose |
|---|---|---|
| 1 | Public website | Present the personal brand and provide navigation to all services |
| 2 | Homepage | Introduce Yemi Adebanjo and highlight key content and services |
| 3 | About and biography | Present approved biography, qualifications, work, and institutional affiliations |
| 4 | Bookstore | Catalogue, search, filter, and display publications |
| 5 | E-commerce | Process book orders, payments, digital delivery, and printed-book fulfilment |
| 6 | Consultation booking | Manage services, availability, appointments, payments, and reminders |
| 7 | Video library | Publish and organise approved video content |
| 8 | Photo gallery | Organise photographs into albums and categories |
| 9 | Awards and recognitions | Display approved awards, credentials, and recognitions |
| 10 | Articles and updates | Publish articles, teachings, announcements, and professional updates |
| 11 | Speaking and training | Receive and manage speaking invitations |
| 12 | Testimonials | Manage permissions, reviews, and publication approval |
| 13 | Events and appearances | Publish upcoming and past events |
| 14 | Contact management | Collect and manage enquiries |
| 15 | Email subscriptions | Manage consent-based newsletter subscriptions |
| 16 | Payments | Integrate an approved payment gateway |
| 17 | Administration dashboard | Manage website content and business operations |
| 18 | Security and access control | Protect administrative functions, customer information, and digital assets |
| 19 | SEO and analytics | Support discoverability and measure website activity |
| 20 | Policies and settings | Manage legal information and operational configuration |

### 2.2 Proposed Future Enhancements

The following are potential future enhancements rather than mandatory launch requirements unless separately approved:

- A customer account portal for accessing purchases and bookings.
- Advanced customer segmentation and marketing automation.
- A learning management system for structured courses.
- Paid membership and subscription services.
- Online course enrolment and certification.
- A dedicated mobile application.
- Advanced sales and marketing reports.
- Additional languages.
- Automated shipping-provider integrations.
- A multi-author publishing workflow.

These enhancements should not delay the initial release unless they become explicit business requirements.

---

## 3. Target Users and User Roles

### 3.1 Public Visitor

A public visitor can:

- View the homepage and biography.
- Browse books and their details.
- Read publicly available articles.
- Watch videos.
- Browse photo albums.
- View approved awards and testimonials.
- View events.
- Submit a contact enquiry.
- Submit a speaking invitation.
- Book a consultation.
- Purchase available books.
- Subscribe to email updates.

A public visitor must not have access to private administrative pages, confidential enquiries, unpublished content, protected digital books, or another customer's private information.

### 3.2 Book Customer

A customer purchasing books can:

- Add available books to a shopping cart.
- Select the desired book formats.
- Review prices and applicable delivery charges.
- Provide the necessary contact and delivery details.
- Complete checkout.
- Receive an order confirmation and receipt.
- Access eligible digital downloads.
- Receive printed-order updates.
- Request assistance with an eligible download or order.

Account registration should not be mandatory for an ordinary purchase.

### 3.3 Consultation Client

A consultation client can:

- Review consultation services.
- Compare session durations, fees, and delivery methods.
- View available appointment slots.
- Select a suitable time.
- Submit contact information and consultation details.
- Pay where required.
- Receive a booking confirmation.
- Receive meeting details and reminders.
- Reschedule or cancel according to the applicable policy.

### 3.4 Website Owner

The website owner has the highest level of authorised access.

The owner can manage:

- Website content and branding.
- Books, prices, stock, and orders.
- Consultation services and appointments.
- Videos, photographs, and articles.
- Awards and credentials.
- Testimonials and approval decisions.
- Speaking invitations and enquiries.
- Events and subscribers.
- Website settings, policies, and integrations.
- Administrative users and permissions.

### 3.5 Content Manager

A content manager assists with routine publishing activities.

Depending on assigned permissions, this role can manage:

- Articles and updates.
- Videos and photo galleries.
- Homepage content.
- Biography content.
- Events and approved media.
- Draft content awaiting publication.

The role must not automatically receive access to payment credentials, confidential customer records, refunds, or administrative security settings.

### 3.6 Order and Booking Manager

An authorised operational team member may be allowed to manage:

- Book orders.
- Delivery information.
- Stock adjustments.
- Booking schedules.
- Consultation confirmations.
- Customer communications.
- Enquiry follow-ups.

Permissions should be configurable rather than granting unrestricted administrative access.

**Role design recommendation:** Use Laravel authorization policies and gates, supported by role-based permissions. Every sensitive action must be authorised on the server, not merely hidden in the frontend.

---

## 4. Technology Stack and Architecture

The proposed stack is Laravel + Inertia.js + React + TypeScript (TSX) + MySQL.

This architecture allows the application to use Laravel for backend business logic, authentication, database access, payments, and integrations while delivering a modern React-based user interface.

### 4.1 Recommended Technology Stack

| Layer | Technology | Responsibility |
|---|---|---|
| Backend framework | Laravel | Application logic, APIs, security, validation, and business processes |
| Frontend framework | React | User interface and interactive components |
| Frontend language | TypeScript (TSX) | Typed React components and frontend logic |
| Application bridge | Inertia.js | Connects Laravel routes and controllers to React pages |
| Database | MySQL | Stores application and business data |
| Styling | Tailwind CSS | Responsive design and interface styling |
| UI components | shadcn/ui, where appropriate | Reusable accessible interface components |
| Authentication | Laravel authentication with session-based login | User authentication and session management |
| Authorization | Laravel policies, gates, and role-based permissions | Restricts access to protected features |
| Background jobs | Laravel Queues | Processes email, reminders, and other asynchronous tasks |
| Scheduling | Laravel Scheduler | Runs scheduled publishing, reminders, and maintenance jobs |
| Cache | Redis, where supported | Caching, queue processing, and performance optimisation |
| Payments | An approved gateway, such as Paystack or Flutterwave | Online payments and transaction verification |
| Email | An approved transactional email provider | Receipts, confirmations, notifications, and password resets |
| File storage | Private server storage or compatible object storage | Digital books, private documents, and media |
| Video delivery | Approved external video hosting | Video embedding and playback |
| Testing | Pest or PHPUnit; React Testing Library where useful | Backend, frontend, and integration testing |
| Version control | Git | Source control and collaborative development |
| Deployment | Linux hosting or a compatible cloud server | Production hosting and application deployment |

**Important:** These are proposed implementation choices, not confirmed existing services. The payment provider, email service, hosting environment, video hosting, and storage provider must be selected and configured before the relevant production features can be activated.

### 4.2 Application Architecture

The application will follow a modular, Laravel-based architecture.

```text
                 PUBLIC VISITORS
                        |
                        v
              REACT + INERTIA.JS
                 TYPESCRIPT (TSX)
                        |
                        v
                 LARAVEL ROUTES
                        |
                        v
               LARAVEL CONTROLLERS
                        |
           +------------+-------------+
           |            |             |
           v            v             v
       SERVICES      POLICIES      VALIDATION
           |
           +------------------------------+
           |              |               |
           v              v               v
       E-COMMERCE     BOOKINGS         CONTENT
           |              |               |
           +--------------+---------------+
                          |
                          v
                       MYSQL
                          |
           +--------------+--------------+
           |              |              |
           v              v              v
       EMAIL         PAYMENT         FILE STORAGE
       PROVIDER      GATEWAY         AND MEDIA
```

#### Architectural principles

1. **Server-side business logic:** Prices, permissions, payment status, availability, and order fulfilment must be enforced by Laravel.
2. **Reusable components:** Common interface elements such as navigation, book cards, forms, buttons, and dashboard tables should be reusable.
3. **Separation of concerns:** Controllers should coordinate requests, while dedicated services handle complex operations.
4. **Secure file delivery:** Paid digital publications must not be accessible through unrestricted public URLs.
5. **Consistent data management:** The same source of truth must drive the public website and administration dashboard.
6. **Scalability:** New content categories, books, services, and business features should be added without rebuilding the entire application.

---

## 5. Functional Requirements

This section defines what the application must do.

Each requirement has an identifier that can be used during development, testing, and acceptance.

### 5.1 Public Website and Homepage

**Module ID: PUB-01**

The homepage must introduce Yemi Adebanjo and direct visitors towards the most important activities.

| ID | Requirement | Priority |
|---|---|---|
| PUB-001 | Display the name Yemi Adebanjo and supporting brand line | Must have |
| PUB-002 | Display an approved profile photograph and introductory statement | Must have |
| PUB-003 | Provide navigation to About, Books, Consultations, Videos, Articles, Events, and Contact | Must have |
| PUB-004 | Display selected books and link to individual book pages | Must have |
| PUB-005 | Display a featured video | Must have |
| PUB-006 | Display recent articles and announcements | Must have |
| PUB-007 | Display selected photographs and approved awards | Must have |
| PUB-008 | Display approved testimonials | Must have |
| PUB-009 | Provide working consultation and speaking enquiry buttons | Must have |
| PUB-010 | Provide an email subscription form | Must have |
| PUB-011 | Display contact details and policy links in the footer | Must have |
| PUB-012 | Allow administrators to manage homepage sections and featured content | Must have |

#### Suggested homepage layout

1. Header and navigation.
2. Hero section introducing Yemi Adebanjo.
3. Biography preview.
4. Featured books.
5. Consultation services.
6. Featured teaching or video.
7. Latest articles and updates.
8. Speaking and training section.
9. Selected recognitions.
10. Approved testimonials.
11. Newsletter subscription.
12. Footer.

The homepage should remain focused. Full collections should be displayed on their dedicated pages.

### 5.2 About and Biography Management

**Module ID: BIO-01**

The application must provide a complete profile page that communicates the owner's approved background, professional interests, ministry, and institutional work.

Requirements:

- Support a full biography and a shorter biography.
- Support multiple approved photographs.
- Support profile sections for different areas of work.
- Display verified qualifications, memberships, and achievements.
- Provide a downloadable speaker profile or media kit.
- Link to relevant books, consultations, events, and media appearances.
- Allow authorised administrators to update profile content.
- Support publication and removal of downloadable documents.

The system must not generate or publish unverified credentials or professional claims.

### 5.3 Bookstore and Catalogue

**Module ID: BOOK-01**

The bookstore must support the management of the owner's current and future publications.

#### Book fields

| Field | Description |
|---|---|
| ID | Unique database identifier |
| Title | Book title |
| Slug | SEO-friendly URL identifier |
| Subtitle | Optional subtitle |
| Author | Author's name |
| Description | Full book description |
| Cover image | Approved book cover |
| Category | Book category |
| Topics | Relevant subjects or keywords |
| Formats | Digital and/or printed formats |
| Price | Price associated with each format |
| Currency | Currency in which the price is offered |
| ISBN | Optional publication identifier |
| Publisher | Publisher details where applicable |
| Publication date | Publication date where known |
| Sample file | Optional authorised preview |
| Table of contents | Optional supporting document or content |
| Stock quantity | Available printed copies |
| Featured status | Whether the book appears in featured sections |
| Publication status | Draft, published, or unpublished |
| Sales status | Free, available for purchase, forthcoming, or display only |

The implementation should separate publication status from sales status. For example, a published book can be unavailable for purchase, and a forthcoming book can be displayed without being purchasable.

#### Bookstore functionality

Visitors must be able to:

- Search books by title.
- Filter by category, subject, format, and availability.
- View individual book details.
- Preview authorised sample content.
- Select a format.
- See the corresponding price and currency.
- Add purchasable books to the cart.
- View related books.

Administrators must be able to create, edit, publish, unpublish, and archive books.

**Acceptance criterion:** A book marked as display-only or forthcoming cannot be purchased through the normal checkout process.

### 5.4 Shopping Cart and Checkout

**Module ID: SHOP-01**

The checkout process must support the purchase of one or more books.

#### Customer flow

```text
Browse Books
     |
     v
View Book Details
     |
     v
Select Format
     |
     v
Add to Cart
     |
     v
Review Cart
     |
     v
Enter Contact Details
     |
     v
Enter Delivery Details
(if physical books are included)
     |
     v
Review Total and Currency
     |
     v
Proceed to Payment
     |
     v
Payment Gateway
     |
     v
Server-Side Verification
     |
     v
Create/Confirm Paid Order
     |
     +----------------------+
     |                      |
     v                      v
Digital Delivery       Physical Fulfilment
```

Requirements:

- Support guest checkout.
- Validate the availability of each item.
- Calculate totals on the server.
- Calculate applicable delivery charges.
- Clearly display the currency and total before payment.
- Create a unique order reference.
- Record the transaction status.
- Prevent duplicate order processing.
- Send an order confirmation after the appropriate status transition.
- Provide an order record in the administration dashboard.

A browser redirect from a payment gateway must not, by itself, be treated as proof of successful payment.

### 5.5 Digital Book Delivery

**Module ID: BOOK-02**

Digital purchases must be delivered securely.

Requirements:

- Store paid digital publications in protected storage.
- Verify payment before granting access.
- Generate protected download links or authenticated download routes.
- Apply configurable link expiry or reasonable download limits.
- Record download activity where appropriate.
- Allow administrators to manage download access.
- Provide a process for legitimate customers to request renewed access.
- Send delivery instructions by email.
- Prevent customers from accessing other customers' purchased files.

#### Recommended implementation

Use Laravel storage with a private disk or private object storage. Serve files through an authorised download controller or short-lived signed links.

Do not store protected e-books in a publicly accessible directory such as `public/storage` merely to make downloading convenient.

### 5.6 Printed Book Fulfilment

**Module ID: BOOK-03**

The application must support physical-book orders.

Requirements:

- Collect the customer's name and contact details.
- Collect the delivery address.
- Support configured delivery options and charges.
- Show estimated delivery periods before payment.
- Track stock.
- Record order fulfilment status.
- Allow authorised administrators to add shipping references.
- Send appropriate status updates.

#### Proposed order statuses

- Pending payment.
- Payment processing.
- Paid.
- Processing.
- Ready for dispatch.
- Shipped.
- Delivered.
- Cancelled.
- Refund pending.
- Partially refunded.
- Refunded.

The exact workflow can be adjusted to match the owner's fulfilment process.

### 5.7 Consultation Services and Booking

**Module ID: BOOKING-01**

The consultation module must allow the owner to offer multiple services with different durations, prices, and delivery methods.

#### Initial service categories

1. Leadership and management.
2. Business direction and organisational structure.
3. Ministry structure and institutional development.
4. Succession and continuity.
5. Writing, authorship, and publishing direction.

These are proposed initial categories from the supplied brief. The owner must approve the final list.

#### Consultation service fields

- Service name.
- Slug.
- Description.
- Intended audience.
- Session duration.
- Fee.
- Currency.
- Delivery method.
- Preparation instructions.
- Cancellation policy.
- Rescheduling policy.
- Publication status.

#### Booking flow

```text
Consultation Page
       |
       v
Select Service
       |
       v
View Available Dates
       |
       v
Select Time Slot
       |
       v
Enter Contact Information
       |
       v
Describe Consultation Needs
       |
       v
Review Booking Details
       |
       v
Complete Required Payment
       |
       v
Verify Payment and Availability
       |
       v
Confirm Appointment
       |
       v
Send Confirmation and Calendar Invite
       |
       v
Send Meeting Details and Reminders
```

#### Availability management

The owner must be able to configure:

- Working days.
- Working hours.
- Appointment duration.
- Breaks between appointments.
- Preparation time.
- Blocked dates.
- Holidays.
- Temporary unavailability.
- Maximum bookings per period.

The system must store appointment times consistently and display them in the visitor's selected or detected time zone.

#### Double-booking prevention

Availability must be checked on the server when a booking is created.

The implementation should also use database transactions and appropriate locking or database constraints to prevent concurrent requests from booking the same slot.

For paid consultations, a temporary reservation may be held during checkout. The hold must expire if payment is not completed within the configured period.

**Acceptance criterion:** Two customers cannot obtain confirmed bookings for the same unavailable time slot.

### 5.8 Video Library

**Module ID: VIDEO-01**

The video library must support approved videos relating to teachings, leadership, interviews, speaking engagements, and publications.

Requirements:

- Featured video section.
- Video categories.
- Search functionality.
- Thumbnail images.
- Video titles and descriptions.
- Publication dates.
- Individual shareable video pages.
- Related videos.
- Optional transcripts and captions.
- Mobile-compatible playback.
- No automatic playback with sound.
- Dashboard controls for creating, editing, featuring, unpublishing, and removing videos.

#### Recommended approach

Start with embedded videos from an approved external video platform.

Direct video uploads and streaming should be introduced only after storage, bandwidth, transcoding, and streaming costs have been assessed.

### 5.9 Photo Gallery

**Module ID: GALLERY-01**

The gallery must organise photographs into albums.

Requirements:

- Create albums.
- Edit album descriptions.
- Upload multiple photographs.
- Select album cover images.
- Add captions and image credits.
- Record event dates and locations where appropriate.
- Reorder photographs.
- View full-size photographs.
- Navigate between photographs.
- Generate optimised image sizes.
- Provide alternative text.
- Publish or unpublish albums.
- Control whether specific images can be downloaded.

Public downloads should be disabled by default.

### 5.10 Awards and Recognitions

**Module ID: AWARD-01**

The awards module must distinguish different types of professional recognition.

Supported categories:

- Awards and honours.
- Academic qualifications.
- Professional certifications.
- Professional memberships.
- Other verified achievements.

Each record should support the title, issuing organisation, date, description, relevant photographs, certificate image, and supporting link.

Administrators must approve each entry before publication.

Sensitive identifiers on certificate images must be concealed before publication.

### 5.11 Articles, Teachings, and Updates

**Module ID: ARTICLE-01**

The application must provide a publishing system for regular content updates.

#### Initial categories

- Faith and life.
- Leadership and management.
- Business and institution building.
- Writing and publishing.
- Ministry development.
- Personal and professional updates.

#### Article fields

- Title.
- Slug.
- Summary.
- Body content.
- Featured image.
- Category.
- Tags.
- Author.
- Publication date.
- Publication status.
- Embedded media.
- Related links.
- SEO title and description.

#### Editorial workflow

```text
Create Article
      |
      v
Save as Draft
      |
      v
Preview
      |
      v
Publish Immediately
       OR
Schedule Publication
      |
      v
Public Article Page
```

The dashboard must support editing published articles, scheduling posts, and unpublishing content.

### 5.12 Speaking and Training Invitations

**Module ID: SPEAK-01**

The speaking page must help organisations submit structured invitations.

#### Form fields

- Contact person.
- Organisation.
- Email address.
- Telephone number.
- Event name.
- Event purpose.
- Proposed date.
- Time zone.
- Venue or online platform.
- Country and location.
- Audience type.
- Estimated audience size.
- Requested topic.
- Expected speaking duration.
- Budget or honorarium, where applicable.
- Travel and accommodation arrangements.
- Additional information.

#### Dashboard workflow

Each invitation should have a status:

- New.
- Under review.
- Follow-up required.
- Accepted.
- Declined.
- Completed.
- Closed.

The owner or authorised manager should be able to add internal notes and record follow-up actions.

The public page must explain that submitting an invitation does not confirm availability or acceptance.

### 5.13 Testimonials and Reviews

**Module ID: REVIEW-01**

The application must support genuine, approved testimonials.

Requirements:

- Record the contributor's name.
- Record their approved role or organisation.
- Store the written testimonial.
- Optionally store an approved photograph or video.
- Record permission to publish the testimonial and associated media.
- Allow administrators to approve or reject submissions.
- Support featuring approved testimonials.
- Allow testimonials to be unpublished.

Only approved testimonials should appear publicly.

### 5.14 Events and Appearances

**Module ID: EVENT-01**

The events module must support both upcoming and past appearances.

Event fields:

- Event title.
- Description.
- Date and time.
- Time zone.
- Venue or online information.
- Promotional image.
- Registration or enquiry link.
- Event status.
- Related photographs.
- Related videos.
- Related articles.

The application must distinguish public events from private appointments and confidential engagements.

### 5.15 Contact and Enquiry Management

**Module ID: CONTACT-01**

The contact page must allow visitors to submit enquiries.

Enquiry categories:

- Books.
- Consultations.
- Speaking.
- Media.
- Partnerships.
- General enquiries.

Requirements:

- Validate form inputs.
- Provide spam protection.
- Record submissions in the database.
- Send a submission acknowledgement.
- Notify the appropriate authorised recipient.
- Display enquiries in the dashboard.
- Support internal notes and status changes.
- Restrict confidential enquiry information to authorised users.

Suggested statuses are New, In Progress, Awaiting Response, Resolved, and Closed.

### 5.16 Email Subscriptions

**Module ID: NEWS-01**

The application must integrate with an actual email marketing service.

Requirements:

- Voluntary subscription.
- Clear consent wording.
- Subscription confirmation.
- Working unsubscribe links.
- Subscriber export.
- Interest-based subscriber groups.
- Consent records.
- Branded email templates.
- Synchronisation with the selected email marketing provider.

Customers and enquiry submitters must not automatically become subscribers.

The integration should record subscription state and consent timestamps. Where supported, the system should use a double opt-in process.

---

## 6. Administration Dashboard

**Module ID: ADMIN-01**

The administration dashboard is one of the most important parts of this application.

The owner should be able to manage the website independently, while team members should receive access only to the functions required for their responsibilities.

### 6.1 Dashboard Navigation

```text
Dashboard
│
├── Website Content
│   ├── Homepage
│   ├── Biography
│   ├── Contact Details
│   └── Policies
│
├── Bookstore
│   ├── All Books
│   ├── Categories
│   ├── Formats and Prices
│   ├── Inventory
│   └── Reviews
│
├── Sales
│   ├── Orders
│   ├── Transactions
│   ├── Digital Downloads
│   ├── Shipping
│   └── Refunds
│
├── Consultations
│   ├── Services
│   ├── Availability
│   ├── Bookings
│   ├── Cancellations
│   └── Reminders
│
├── Media
│   ├── Videos
│   ├── Photo Albums
│   └── Media Library
│
├── Publications
│   ├── Articles
│   ├── Categories
│   └── Scheduled Posts
│
├── Professional Profile
│   ├── Awards
│   ├── Qualifications
│   ├── Testimonials
│   ├── Events
│   └── Speaking Invitations
│
├── Communications
│   ├── Contact Enquiries
│   ├── Subscribers
│   └── Email Integration
│
├── Reports
│   ├── Sales
│   ├── Bookings
│   ├── Enquiries
│   └── Website Activity
│
└── Settings
    ├── Users and Roles
    ├── Payment Settings
    ├── Email Settings
    ├── Website Settings
    ├── Security
    └── Audit Logs
```

### 6.2 Dashboard Overview

The dashboard homepage should display real figures from the database.

Recommended summary cards:

- Total book sales.
- Orders awaiting fulfilment.
- Successful transactions.
- Upcoming consultations.
- Pending consultation payments.
- New speaking invitations.
- Unresolved enquiries.
- Total active subscribers.
- Recent administrative activity.

Reports must use actual data and clearly define their date ranges.

For example, revenue reporting must distinguish successful payments from pending payments, failed transactions, and refunds.

The dashboard must not use hardcoded figures to simulate operational activity.

### 6.3 Administrative Security

The dashboard must include:

- Authentication.
- Password reset.
- Role-based access control.
- Server-side authorization.
- Secure session management.
- Rate limiting on authentication endpoints.
- Two-factor authentication for administrators where supported.
- Audit logs for sensitive actions.
- Secure logout.
- Protection against unauthorised access to files and records.

Sensitive actions such as issuing refunds, changing permissions, and changing payment settings should be restricted to authorised roles.

---

## 7. Database Design

The application will use MySQL.

The following is the proposed logical database structure. The final schema should be refined during technical design and implementation.

### 7.1 User and Access Management

| Table | Purpose |
|---|---|
| `users` | Administrator and optional customer accounts |
| `roles` | User roles |
| `permissions` | Individual permissions |
| `role_user` | User-to-role assignments |
| `permission_role` | Role-to-permission assignments |
| `sessions` | Database-backed sessions, if selected |
| `personal_access_tokens` | API tokens, if required by an actual integration |

The role tables assume a role-permission implementation. If an established Laravel permission package is selected, its recommended schema should be used instead of duplicating it.

### 7.2 Profile and Website Content

| Table | Purpose |
|---|---|
| `site_settings` | Global website settings |
| `profile_sections` | Biography and profile sections |
| `media` | Shared media metadata |
| `speaker_documents` | Speaker profiles and downloadable documents |
| `awards` | Awards and recognitions |
| `credentials` | Qualifications, certifications, and memberships |
| `testimonials` | Testimonials and publication status |
| `testimonial_permissions` | Consent and publication permission records |
| `events` | Upcoming and past events |
| `event_media` | Media associated with events |
| `articles` | Articles and teachings |
| `article_categories` | Article categories |
| `article_tags` | Article tags |
| `article_tag` | Article-to-tag relationships |
| `article_media` | Related article media |

### 7.3 Bookstore

| Table | Purpose |
|---|---|
| `books` | Core publication information |
| `book_categories` | Book categories |
| `book_category` | Book-to-category relationships |
| `book_formats` | Available formats and format-specific information |
| `book_files` | References to protected digital files and previews |
| `book_prices` | Prices by format and currency |
| `book_reviews` | Reader reviews and approval status |
| `inventory_movements` | Records stock changes |

A book and its formats should be separate concepts. The same book might have PDF, EPUB, paperback, and hardcover formats. Each purchasable format may have its own price, availability, SKU, and inventory rules.

### 7.4 E-Commerce

| Table | Purpose |
|---|---|
| `customers` | Customer contact information, if separated from users |
| `carts` | Shopping cart records, if persisted |
| `cart_items` | Items within a cart |
| `orders` | Order header and totals |
| `order_items` | Purchased products, quantities, and price snapshots |
| `transactions` | Payment transaction records |
| `refunds` | Refund records |
| `shipments` | Shipment and delivery information |
| `digital_downloads` | Download entitlements and limits |
| `order_status_history` | Order status changes |

The implementation must preserve purchase-time prices in order items so that later changes to a book's price do not alter historical orders.

Payment notifications must be processed idempotently, meaning repeated notifications for the same transaction cannot create duplicate orders or trigger duplicate fulfilment.

### 7.5 Consultation Booking

| Table | Purpose |
|---|---|
| `consultation_services` | Available consultation offerings |
| `consultation_availability` | Working hours and recurring availability |
| `availability_exceptions` | Blocked dates and special working hours |
| `booking_slots` | Optional generated or managed appointment slots |
| `bookings` | Consultation bookings |
| `booking_status_history` | Booking status changes |
| `booking_payments` | Link between bookings and payment transactions |
| `booking_reminders` | Reminder scheduling and delivery records |

The schema should also support the appointment time zone, meeting details, cancellation reason, rescheduling history, and payment state.

### 7.6 Invitations and Communications

| Table | Purpose |
|---|---|
| `speaking_invitations` | Speaking and training enquiries |
| `contact_enquiries` | General contact enquiries |
| `enquiry_notes` | Internal follow-up notes |
| `newsletter_subscribers` | Subscriber records |
| `subscription_consents` | Subscription consent and audit information |
| `newsletter_interests` | Available subscriber interests |
| `newsletter_subscriber_interest` | Subscriber-to-interest relationships |
| `email_logs` | Relevant transactional email delivery records |

Avoid storing full email bodies or unnecessary sensitive data in operational logs.

### 7.7 Operational Records

| Table | Purpose |
|---|---|
| `audit_logs` | Records of important administrative actions |
| `failed_jobs` | Failed queued jobs |
| `jobs` | Queue jobs when database queues are used |
| `cache` | Cache records when the database cache driver is used |

Laravel may create additional framework tables depending on the selected configuration.

### Database design rules

1. Use primary keys and foreign keys where appropriate.
2. Add indexes to frequently searched fields.
3. Enforce uniqueness on important identifiers, including slugs where required.
4. Use decimal-compatible representations for monetary values rather than floating-point types.
5. Store transaction references and idempotency identifiers.
6. Preserve order and transaction history.
7. Use transactions for multi-step operations that must succeed or fail together.
8. Avoid deleting historical financial records simply because a product or customer record is no longer active.
9. Define data-retention rules for customer and operational records.
10. Protect sensitive personal information with appropriate access controls.

---

## 8. Proposed Laravel Project Structure

I recommend a modular structure that keeps related features organised without introducing unnecessary architectural complexity.

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Public/
│   │   │   ├── HomeController.php
│   │   │   ├── AboutController.php
│   │   │   ├── BookController.php
│   │   │   ├── ArticleController.php
│   │   │   ├── VideoController.php
│   │   │   ├── GalleryController.php
│   │   │   ├── EventController.php
│   │   │   ├── ConsultationController.php
│   │   │   ├── BookingController.php
│   │   │   ├── SpeakingInvitationController.php
│   │   │   └── ContactController.php
│   │   │
│   │   ├── Checkout/
│   │   │   ├── CartController.php
│   │   │   ├── CheckoutController.php
│   │   │   ├── PaymentController.php
│   │   │   └── DownloadController.php
│   │   │
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── BookController.php
│   │       ├── OrderController.php
│   │       ├── BookingController.php
│   │       ├── ArticleController.php
│   │       ├── MediaController.php
│   │       ├── EventController.php
│   │       ├── AwardController.php
│   │       ├── TestimonialController.php
│   │       ├── InvitationController.php
│   │       ├── SubscriberController.php
│   │       └── SettingsController.php
│   │
│   ├── Middleware/
│   └── Requests/
│
├── Models/
│   ├── User.php
│   ├── Book.php
│   ├── BookFormat.php
│   ├── Order.php
│   ├── OrderItem.php
│   ├── Transaction.php
│   ├── Booking.php
│   ├── ConsultationService.php
│   ├── Article.php
│   ├── Event.php
│   ├── Award.php
│   ├── Testimonial.php
│   └── SpeakingInvitation.php
│
├── Services/
│   ├── Payments/
│   ├── Orders/
│   ├── DigitalDelivery/
│   ├── Bookings/
│   ├── Notifications/
│   └── Newsletter/
│
├── Jobs/
├── Mail/
├── Notifications/
├── Policies/
├── Rules/
└── Providers/
```

This is a proposed logical organisation. It does not mean every folder must be created immediately.

### 8.1 Example Frontend Structure

```text
resources/js/
├── app.tsx
│
├── Components/
│   ├── UI/
│   ├── Navigation/
│   ├── Footer/
│   ├── BookCard.tsx
│   ├── ArticleCard.tsx
│   ├── VideoCard.tsx
│   ├── FormField.tsx
│   └── Pagination.tsx
│
├── Layouts/
│   ├── PublicLayout.tsx
│   ├── AdminLayout.tsx
│   └── AuthLayout.tsx
│
├── Pages/
│   ├── Public/
│   │   ├── Home.tsx
│   │   ├── About.tsx
│   │   ├── Books/
│   │   ├── Consultations/
│   │   ├── Videos/
│   │   ├── Gallery/
│   │   ├── Articles/
│   │   ├── Events/
│   │   ├── Speaking.tsx
│   │   └── Contact.tsx
│   │
│   ├── Checkout/
│   │   ├── Cart.tsx
│   │   ├── Checkout.tsx
│   │   └── OrderConfirmation.tsx
│   │
│   ├── Booking/
│   │   ├── Services.tsx
│   │   ├── Availability.tsx
│   │   └── Confirmation.tsx
│   │
│   ├── Admin/
│   │   ├── Dashboard.tsx
│   │   ├── Books/
│   │   ├── Orders/
│   │   ├── Bookings/
│   │   ├── Articles/
│   │   ├── Media/
│   │   ├── Events/
│   │   ├── Awards/
│   │   ├── Testimonials/
│   │   ├── Invitations/
│   │   ├── Subscribers/
│   │   └── Settings/
│   │
│   └── Auth/
│
├── Hooks/
├── Types/
├── Utils/
└── Lib/
```

Use shared TypeScript types for common objects such as books, orders, bookings, pagination metadata, and authenticated users.

---

## 9. Payment Integration Requirements

**Module ID: PAY-01**

The payment system must support the owner's approved business and target markets.

The supplied brief identifies Nigeria and international customers as the target market. The specific payment gateway remains a decision to be confirmed.

Potential providers include:

- [Paystack](https://paystack.com/)
- [Flutterwave](https://flutterwave.com/)

Their actual availability, supported currencies, international payment capabilities, settlement arrangements, fees, and account eligibility must be confirmed before choosing one.

### 9.1 Payment Processing Flow

```text
Customer Starts Checkout
          |
          v
Laravel Validates Cart
          |
          v
Calculate Authoritative Total
          |
          v
Create Pending Order
          |
          v
Create Payment Transaction
          |
          v
Initiate Gateway Checkout
          |
          v
Customer Completes Payment
          |
          v
Gateway Sends Callback/Webhook
          |
          v
Laravel Verifies Transaction
          |
          v
Validate Amount, Currency,
Reference and Order Status
          |
          v
Mark Transaction Successful
          |
          v
Confirm Order or Booking
          |
          v
Trigger Receipt and Fulfilment
```

### 9.2 Payment Security Requirements

- Keep secret keys on the server.
- Never store customers' card details.
- Verify payment using the provider's documented server-side mechanism.
- Verify webhook signatures.
- Check the transaction reference, amount, currency, and applicable order reference.
- Handle duplicate notifications safely.
- Record failed and pending transactions.
- Support appropriate refund handling.
- Ensure that payment success cannot be triggered simply by editing browser data.
- Separate sandbox and production credentials.
- Do not activate live payments until account approval and end-to-end testing are complete.

### 9.3 Currency Handling

If NGN and USD pricing is required:

- Store the price for each supported currency.
- Allow the owner to configure prices independently.
- Clearly identify the checkout currency.
- Use the currency supported by the selected payment configuration.
- Store the actual transaction currency and amount.
- Never silently change a customer's currency during checkout.

**Important:** Showing USD prices on a website does not automatically mean the business can accept or settle USD payments. This depends on the provider's account configuration and supported settlement arrangements.

---

## 10. Email and Notification Requirements

The application needs transactional email and, separately, marketing email.

### 10.1 Transactional Emails

These include:

- Order confirmation.
- Payment receipt.
- Digital download instructions.
- Printed-book order updates.
- Consultation booking confirmation.
- Consultation reminders.
- Booking cancellation and rescheduling.
- Speaking invitation acknowledgement.
- Contact enquiry acknowledgement.
- Password reset and security notifications.

Transactional emails should be triggered by confirmed business events rather than by arbitrary frontend redirects.

### 10.2 Marketing Emails

Marketing communications include:

- New book announcements.
- New articles and teachings.
- Event announcements.
- Other approved newsletters.

These should be managed through an approved email marketing service, with consent and unsubscribe support.

### 10.3 Recommended Implementation

Use Laravel's mail, notification, and queue facilities.

Time-sensitive tasks such as reminders should be scheduled through Laravel Scheduler and processed using queues where appropriate.

The system should record relevant delivery outcomes and provide a safe process for retrying failed jobs.

---

## 11. Security and Privacy Requirements

**Module ID: SEC-01**

Security must be considered throughout development rather than added at the end.

| Area | Requirement |
|---|---|
| Transport security | HTTPS in production |
| Authentication | Secure login, password reset, and session handling |
| Authorization | Server-side role and permission checks |
| Administrator protection | Two-factor authentication where supported |
| Input validation | Validate and normalise user inputs on the server |
| Database security | Use Laravel's parameterised query mechanisms and avoid unsafe raw SQL |
| File uploads | Validate file type, size, and content where feasible |
| Private files | Store paid books and confidential documents outside public web access |
| Payment security | Verify transactions server-side |
| Webhooks | Validate signatures and process notifications idempotently |
| Abuse prevention | Rate-limit sensitive endpoints and protect forms against spam |
| Auditability | Record important administrative and financial actions |
| Backups | Maintain protected backups and test restoration |
| Secrets | Store credentials in environment configuration or a suitable secret manager |
| Data access | Restrict access to private customer and enquiry information |
| Data retention | Define retention and deletion rules |
| Availability | Monitor errors and important operational failures |

### Additional requirements

1. Do not expose secret credentials in frontend JavaScript.
2. Do not rely on hidden buttons as an authorization mechanism.
3. Do not make private downloads publicly accessible.
4. Avoid logging passwords, payment secrets, and unnecessary personal information.
5. Use CSRF protection for applicable session-authenticated state-changing requests.
6. Keep dependencies updated.
7. Restrict production debugging output.
8. Test backup restoration before handover.

Privacy notices and policies must be reviewed against the actual data collected, the selected providers, and the applicable legal requirements.

---

## 12. UI/UX Design Requirements

The supplied brief requests a clean, professional, welcoming, internationally accessible design.

### 12.1 Design Direction

Recommended design characteristics:

- White or near-white background.
- Dark, readable primary text.
- Restrained accent colours.
- Generous whitespace.
- Clear typographic hierarchy.
- Professional photography.
- Consistent buttons and form controls.
- Subtle borders and shadows.
- Accessible contrast.
- Responsive layouts.
- Minimal unnecessary animation.

The final colour palette, typography, and layout should be confirmed against the owner's approved brand assets and design reference.

### 12.2 Public Navigation

Suggested desktop navigation:

**Home | About | Books | Consultations | Videos | Articles | Events | Speaking | Contact**

The header should provide clear access to the bookstore and consultation booking process.

On mobile, use an accessible menu with sufficiently large interactive targets.

### 12.3 Administration Design

The administration dashboard should prioritise efficient management.

Recommended features include:

- Collapsible sidebar.
- Clear page titles.
- Searchable tables.
- Pagination.
- Status badges.
- Filters.
- Bulk actions where appropriate.
- Confirmation dialogs for destructive actions.
- Form validation messages.
- Loading and empty states.
- Responsive tables or mobile-friendly alternatives.

The public website and admin dashboard may share design tokens and basic UI components, but their layouts should serve different user needs.

---

## 13. Non-Functional Requirements

These requirements define how well the application must operate.

### 13.1 Performance

- Use pagination for large catalogues and administrative tables.
- Optimise uploaded images.
- Generate responsive image variants where appropriate.
- Use lazy loading for non-critical media.
- Avoid unnecessary database queries.
- Cache suitable public content.
- Use queues for email and other non-immediate processing.
- Avoid serving large video files directly from the application server without a deliberate streaming strategy.

**Proposed performance target:** Aim for good Core Web Vitals and a mobile Largest Contentful Paint of 2.5 seconds or less at the 75th percentile, subject to hosting, media, network conditions, and third-party scripts.

This is a target to measure, not a guarantee.

### 13.2 Scalability

The system should support an expanding book catalogue, more articles, additional consultation services, and growing traffic.

The architecture should allow future additions without unnecessarily coupling content publishing to payments or booking operations.

### 13.3 Accessibility

Target WCAG 2.2 Level AA where reasonably achievable.

The implementation should include:

- Semantic HTML.
- Keyboard navigation.
- Visible focus indicators.
- Proper form labels.
- Accessible error messages.
- Alternative text for meaningful images.
- Appropriate contrast.
- Captions or transcripts when supplied.
- Accessible modal and menu behaviour.

### 13.4 Browser and Device Support

The website must be tested on current mainstream browsers and on mobile, tablet, and desktop screen sizes.

Testing should include:

- Android devices.
- iPhones and iPads.
- Desktop and laptop browsers.
- Narrow mobile screens.
- Slow or unstable network conditions where practical.

### 13.5 Backup and Recovery

- Schedule database backups.
- Back up essential uploaded content.
- Protect backups from unauthorised access.
- Define backup retention.
- Document restoration steps.
- Test a complete recovery process before handover.

The owner should receive clear information about backup frequency, retention, and any recovery limitations.

---

## 14. SEO and Analytics Requirements

**Module ID: SEO-01**

The application should provide a foundation for search engine discovery.

### SEO requirements

- Unique page titles.
- Meta descriptions.
- Clean, descriptive URLs.
- Canonical URLs where needed.
- XML sitemap.
- Robots directives.
- Social sharing metadata.
- Appropriate structured data.
- Optimised images.
- Descriptive internal links.
- Proper heading hierarchy.

Structured data should be used only when the corresponding information is accurate and supported.

For example, article metadata should describe actual articles. Book information must not include fabricated ISBNs, and review markup must not include invented ratings.

### Analytics requirements

Integrate an agreed analytics service to measure:

- Website visits.
- Popular pages.
- Book detail views.
- Add-to-cart activity.
- Checkout completion.
- Completed purchases.
- Consultation enquiries.
- Completed bookings.
- Speaking invitations.
- Newsletter subscriptions.

Where appropriate, configure privacy and consent controls for analytics and other tracking technologies.

Analytics must not be treated as the authoritative source for actual payment status. Financial records should come from the application's verified transaction records.

---

## 15. Important Business Rules

The following rules must be enforced by the application.

| ID | Business rule |
|---|---|
| BR-001 | Only authorised publications may be sold as part of the owner's catalogue. |
| BR-002 | A book marked as forthcoming or display-only cannot be purchased. |
| BR-003 | Digital books must not be released before payment is verified. |
| BR-004 | Printed orders must respect configured stock and delivery rules. |
| BR-005 | Historical orders must preserve their original prices and currency. |
| BR-006 | Repeated payment notifications must not create duplicate fulfilment actions. |
| BR-007 | An appointment cannot be confirmed without valid availability and any required payment. |
| BR-008 | Two confirmed bookings cannot occupy the same unavailable slot. |
| BR-009 | Booking changes must follow the published cancellation and rescheduling policy. |
| BR-010 | A speaking enquiry does not automatically confirm an engagement. |
| BR-011 | Testimonials require approval and appropriate publication permission. |
| BR-012 | Awards and credentials require owner approval before publication. |
| BR-013 | Subscribers must provide consent before marketing communications are sent. |
| BR-014 | Unsubscribed contacts must not continue receiving ordinary marketing emails. |
| BR-015 | Ordinary visitors cannot access the administration dashboard. |
| BR-016 | Sensitive administrative actions require appropriate permissions. |
| BR-017 | Dashboard reports must be calculated from real records. |
| BR-018 | Payment secrets and private files must not be exposed publicly. |
| BR-019 | Private appointments and confidential engagements must not appear publicly. |
| BR-020 | External integrations must be identified as pending until configured and tested. |

---

## 16. Proposed Development Phases

I recommend implementing the application in phases rather than building every feature simultaneously.

The following schedule is an initial estimate for planning purposes, assuming timely access to approved content, stakeholder feedback, hosting, and third-party accounts.

### Phase 1: Discovery and Foundation

**Estimated duration: 1 week**

Tasks:

- Review and approve the PRD.
- Confirm the design reference and brand assets.
- Confirm the final technology and deployment environment.
- Set up the Laravel application.
- Configure Inertia.js, React, TypeScript, and Tailwind CSS.
- Configure MySQL.
- Establish Git repository and branching workflow.
- Define authentication and authorization.
- Establish the initial database schema.

**Deliverable:** A working application foundation with the agreed development conventions.

### Phase 2: Public Website and Content Management

**Estimated duration: 2 weeks**

Tasks:

- Build public navigation and layouts.
- Build the homepage.
- Build About and biography pages.
- Build articles and categories.
- Build video library.
- Build photo gallery.
- Build awards and recognitions.
- Build events and testimonials.
- Implement corresponding administration features.

**Deliverable:** A functional public website and content-management foundation.

### Phase 3: Bookstore and E-Commerce

**Estimated duration: 2 weeks**

Tasks:

- Implement books and categories.
- Implement formats and currency-specific prices.
- Implement book detail pages and search.
- Implement cart and checkout.
- Implement payment integration.
- Implement transaction verification.
- Implement protected digital delivery.
- Implement printed-book inventory and order management.
- Implement receipts and order notifications.

**Deliverable:** A functioning bookstore with tested purchase and delivery flows.

### Phase 4: Consultation Booking

**Estimated duration: 1–2 weeks**

Tasks:

- Implement consultation services.
- Implement availability management.
- Implement appointment selection.
- Implement time-zone handling.
- Implement booking conflict prevention.
- Implement booking payments where required.
- Implement confirmations and reminders.
- Implement cancellation and rescheduling.

**Deliverable:** A functioning consultation booking system.

### Phase 5: Invitations and Communications

**Estimated duration: 1 week**

Tasks:

- Implement speaking invitation forms.
- Implement contact forms.
- Implement enquiry management.
- Integrate transactional email.
- Integrate the approved email marketing service.
- Implement subscription consent and unsubscribe.
- Implement relevant notifications.

**Deliverable:** Working enquiry, speaking invitation, and newsletter workflows.

### Phase 6: Security, Testing, and Launch

**Estimated duration: 1–2 weeks**

Tasks:

- Perform functional and integration testing.
- Test role-based access.
- Test payment failure and success scenarios.
- Test booking concurrency.
- Test digital delivery restrictions.
- Test mobile responsiveness and accessibility.
- Configure production hosting and HTTPS.
- Configure backups and operational logging.
- Configure analytics and SEO.
- Complete owner training.
- Document deployment and recovery.
- Perform production acceptance testing.

**Deliverable:** A tested application ready for production, subject to approved content and external service readiness.

### Overall planning estimate

Approximately **8–10 weeks**, depending on the final scope, design complexity, payment integration, content readiness, and external approvals.

This is a planning estimate, not a fixed delivery commitment. The final schedule should be confirmed after the technical discovery and design review.

---

## 17. Testing and Acceptance Criteria

Every major module must be tested before the application is considered complete.

### 17.1 Bookstore Testing

The team must demonstrate that:

- Visitors can search and browse books.
- Each published book displays its correct information.
- Each format displays the correct price and currency.
- Unavailable books cannot be purchased.
- Cart totals are calculated correctly.
- Stock is handled correctly.
- Successful payments result in the correct order status.
- Failed and pending payments do not trigger paid fulfilment.
- Digital downloads are protected.
- Printed orders display correct delivery charges.

### 17.2 Booking Testing

The team must demonstrate that:

- Visitors can select a consultation service.
- Availability reflects working hours and blocked dates.
- Times are displayed correctly for the relevant time zone.
- Conflicting bookings are prevented.
- Required payment is verified before confirmation.
- Confirmation emails and reminders are sent.
- Cancellation and rescheduling follow the configured policy.

### 17.3 Administration Testing

The team must demonstrate that:

- The owner can manage all agreed content modules.
- Assigned users can access only authorised features.
- Unauthorised visitors cannot access protected pages.
- Dashboard statistics reflect real database records.
- Important actions are logged appropriately.
- Private files remain protected.
- Validation errors are clear and actionable.

### 17.4 Communications Testing

The team must demonstrate that:

- Contact forms validate their inputs.
- Speaking invitations are recorded.
- Confirmation messages are sent.
- Newsletter subscription works.
- Unsubscribe works.
- Marketing consent is recorded.
- Email delivery failures can be investigated.

### 17.5 Production Readiness

Before launch, confirm:

- Production HTTPS is active.
- Live payment credentials are securely configured.
- Payment account approval is complete.
- Email delivery has been tested.
- Backups are running.
- Restoration has been tested.
- SEO metadata and sitemap are configured.
- Analytics is connected where approved.
- Required policies have been reviewed.
- The owner has received administrative access.
- The source code and deployment documentation have been handed over.

---

## 18. Content and Materials Required from the Owner

Development can begin with placeholder content for layout testing, but the final public website must use approved materials.

| Material | Required details | Status |
|---|---|---|
| Brand identity | Approved logo, colours, typography, and design reference | Pending confirmation |
| Biography | Full and short approved biographies | Required |
| Portraits | Approved professional photographs | Required |
| Institutional information | Approved descriptions of Jolax Global Concept and Jolax Global Leadership Center | Required |
| Books | Titles, descriptions, covers, formats, prices, and publication details | Required |
| Digital files | Authorised PDF, EPUB, or other supported files | Required for digital sales |
| Printed books | Stock quantities and fulfilment arrangements | Required for physical sales |
| Video content | Approved video URLs or files | Required for final content |
| Photo gallery | Approved photographs, captions, and permissions | Required |
| Awards | Verified details and approved supporting images | Required |
| Qualifications | Verified credentials and approved publication details | Required |
| Testimonials | Genuine feedback and publication permissions | Required |
| Consultation services | Descriptions, fees, durations, availability, and policies | Required |
| Speaking services | Approved speaking topics and invitation preferences | Required |
| Contact information | Approved email addresses, telephone numbers, and social links | Required |
| Payment gateway | Approved business account and integration access | Required for live payments |
| Email provider | Approved transactional and marketing email services | Required |
| Policies | Approved privacy, terms, delivery, refund, and booking policies | Required |
| Analytics | Selected analytics provider and account access | Required if analytics is enabled |
| Hosting | Domain, hosting, storage, and deployment access | Required for launch |

Passwords and secret credentials should be supplied through secure access or secret-management mechanisms, not ordinary messages or source code.

---

## 19. Risks and Mitigation

| Risk | Potential impact | Mitigation |
|---|---|---|
| Delayed content delivery | Launch delays | Collect and approve content early |
| Payment account approval delays | Live checkout cannot launch | Start provider onboarding during development |
| Unclear book ownership or selling rights | Legal and reputational risk | Verify publication and distribution rights |
| Incomplete delivery arrangements | Physical orders cannot be fulfilled reliably | Define delivery zones, charges, and fulfilment procedures |
| Double-booking errors | Poor client experience | Enforce server-side availability checks and transactional booking logic |
| Digital file exposure | Unauthorised distribution | Use private storage and protected delivery |
| Poor email deliverability | Customers miss confirmations | Configure and test the selected provider and domain authentication |
| Unverified public claims | Reputational risk | Require owner approval for credentials, testimonials, and awards |
| Uncontrolled administrator access | Data exposure or unauthorised changes | Implement least-privilege access and audit logs |
| Unexpected recurring costs | Increased operating expenses | Document hosting, storage, email, video, and gateway costs before launch |
| Incomplete backups | Data loss | Automate backups and test restoration |

---

## 20. Handover and Ownership

At the end of development, the owner should receive:

1. Full source code.
2. Repository access.
3. Production administrator access.
4. Database and deployment configuration documentation.
5. A list of third-party services and recurring costs.
6. Payment and email integration documentation.
7. Backup and recovery instructions.
8. A record of known limitations and pending integrations.
9. A practical dashboard training session.
10. A clear maintenance and support agreement.

The domain, hosting, payment accounts, email services, and essential business infrastructure should be registered under the owner's ownership or an approved business account.

---

## 21. Recommended MVP Release

Although the full PRD describes a comprehensive platform, I recommend dividing implementation into a minimum viable product (MVP) and subsequent enhancements.

The MVP should be substantial enough to establish the brand and support the primary business processes.

### MVP features

- Professional homepage.
- About and biography pages.
- Book catalogue and book detail pages.
- Shopping cart and checkout.
- Verified payment integration.
- Secure digital book delivery.
- Basic printed-book order management.
- Consultation services and booking.
- Speaking invitation form.
- Contact form.
- Articles and updates.
- Video library.
- Photo gallery.
- Basic awards and testimonials.
- Newsletter subscription integration.
- Secure administration dashboard.
- Role-based permissions.
- SEO foundations.
- Analytics integration, where approved.
- Security, backups, and production deployment.

Advanced marketing automation, a dedicated customer portal, complex shipping integrations, memberships, and online courses can be considered after the core platform is stable.

---

## 22. Final Product Acceptance Definition

The application will be considered ready for handover when the agreed features have been implemented, tested, and demonstrated.

The owner must be able to manage the website independently, while visitors must be able to browse content, purchase eligible books, access their digital purchases, book consultations, submit invitations, and subscribe to communications through working end-to-end processes.

Payment processing, email delivery, appointment confirmation, and digital access must rely on verified backend states rather than frontend assumptions.

Any feature awaiting content, account approval, configuration, or an external provider must be documented as pending and excluded from claims of production readiness.

---

## 23. Technical Recommendations Before Development

### 23.1 Use Laravel as the Single Primary Backend

Keep the main application in Laravel. Use Inertia.js to connect Laravel routes and controllers to React TSX pages rather than introducing a separate frontend application and a separate backend API without a specific need.

This simplifies authentication, routing, validation, and deployment.

### 23.2 Build the Admin Dashboard as Part of the Same Application

The public website and dashboard should share the same Laravel backend and database.

Use separate layouts, routes, and authorization rules for public pages and administration.

### 23.3 Implement E-Commerce Carefully

The bookstore is not just a collection of products. It requires product formats, pricing, stock, orders, transactions, refunds, shipping, and protected downloads.

Build these as distinct but connected parts of the application.

### 23.4 Treat Booking Availability as a Business-Critical Feature

Do not simply generate a list of times in React and accept whatever the visitor selects.

Laravel must revalidate availability during booking creation, with concurrency protection to prevent conflicting appointments.

### 23.5 Keep Digital Assets Private by Default

Paid books, confidential documents, and private customer information should be stored and delivered through protected mechanisms.

### 23.6 Prioritise a Clean, Maintainable Codebase

Use Form Request validation, policies, service classes where appropriate, database transactions, queues, and automated tests.

Avoid putting all payment, booking, and delivery logic into large controllers.

### 23.7 Build with Future Expansion in Mind

The initial architecture should make it possible to introduce courses, memberships, advanced marketing, or a customer portal later without requiring a complete rewrite.

---

## 24. Next Step: Technical Requirements Document

With the PRD approved, the next deliverable should be a Technical Requirements Document (TRD) for Laravel + Inertia.js + React TSX + MySQL.

The TRD should define:

- Exact database migrations and relationships.
- Laravel routes and controller responsibilities.
- The Inertia page inventory.
- TypeScript interfaces and frontend component responsibilities.
- Authentication and role-permission implementation.
- Payment gateway integration and webhook processing.
- Digital file storage and download security.
- Booking availability algorithms and concurrency handling.
- Email notifications and queue jobs.
- Automated testing requirements.
- Environment configuration and deployment.
- Development milestones and acceptance tests.

The TRD should serve as the implementation reference for the development team.
