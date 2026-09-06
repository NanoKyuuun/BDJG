# BDJG — Visual Design System

**File:** `DESIGN-v1.md`  
**Version:** 1.0.0  
**Referensi:** (https://mondragon-2.framer.website/demo-6)  
**Dashboard visual baseline:** `tamplate-dashboard/hitam-dashboard.html` (BDJG Studio OS — Black Edition)  
**Status:** Baseline Perancangan Visual  
**Product:** BDJG Creative Studio Website & Studio Management System  
**Primary reference direction:** premium cinematic creative-studio websites, editorial portfolio layouts, motion-led interaction, dengan Mondragon Demo 6 sebagai salah satu referensi utama.  
**Scope:** brand direction, visual language, typography, color, layout, media, motion, interaction, component rules, public website, client portal, worker workspace, admin dashboard, responsive rules, accessibility, dan design QA.  
**Out of scope:** tahapan coding, framework, database implementation, API implementation, deployment, dan urutan development.

---

# 1. Fungsi Dokumen

`blueprint.md` menjawab:

> **Apa yang sistem BDJG lakukan?**

`DESIGN-v1.md` menjawab:

> **Bagaimana BDJG harus terlihat, terasa, bergerak, dan berinteraksi?**

Dokumen ini menjadi sumber acuan visual untuk seluruh surface BDJG:

```text
PUBLIC WEBSITE
CLIENT PORTAL
WORKER WORKSPACE
ADMIN / OWNER
```

Jika ada konflik antara desain visual dan business rule di `blueprint.md`, business rule tetap menjadi sumber kebenaran.

---

# 2. Design Intent

BDJG bukan sekadar website portfolio dan bukan sekadar dashboard bisnis.

Arah pengalaman keseluruhan:

```text
FILM TITLE SEQUENCE
+
EDITORIAL MAGAZINE
+
CREATIVE STUDIO PORTFOLIO
+
PREMIUM PRODUCTION WORKSPACE
```

Public Website harus membuat visitor merasa:

> Studio ini punya taste.

Client Portal:

> Project saya tertata dan profesional.

Worker Workspace:

> Saya tahu apa yang harus saya kerjakan.

Admin / Owner:

> Saya tahu apa yang sedang terjadi di studio.

---

# 3. Experience Zones

## 3.1 Public Website

Prioritas:

```text
Emotion
Brand
Portfolio
Media
Storytelling
Conversion
```

Karakter:

```text
cinematic
editorial
experimental
asymmetrical
motion-led
media-first
```

Ekspresi brand: **100%**

---

## 3.2 Client Portal

Prioritas:

```text
Trust
Project status
Action
Payment
Preview
Revision
Delivery
```

Karakter:

```text
premium
calm
structured
clear
controlled
```

Ekspresi brand: **40–50%**

---

## 3.3 Worker Workspace

Prioritas:

```text
Task
Deadline
Schedule
Files
Revision
Production context
```

Karakter:

```text
focused
compact
efficient
low-noise
```

Ekspresi brand: **15–20%**

---

## 3.4 Admin / Owner

Prioritas:

```text
Attention
Control
Status
Data
Finance
Decision making
```

Karakter:

```text
operational
dense
clear
data-forward
stable
```

Ekspresi brand: **5–10%**

---

# 4. Core Brand Personality

BDJG harus terasa:

```text
CINEMATIC
BOLD
PRECISE
MODERN
EDITORIAL
PREMIUM
CREATIVE
TECHNICAL
CONFIDENT
CALM
```

BDJG tidak boleh terasa:

```text
GENERIC AGENCY
SaaS LANDING PAGE
DOCUMENTATION SITE
CYBERPUNK
NEON-HEAVY
CHILDISH
OVERLY PLAYFUL
CLUTTERED
TEMPLATE-LIKE
```

---

# 5. Visual DNA

BDJG mengadopsi energi dari referensi creative-studio modern:

```text
dark editorial composition
large display typography
small technical metadata
media overlap
asymmetrical project layout
curved section transitions
scroll-led storytelling
hover video previews
controlled motion
```

Identitas original BDJG diperkuat lewat:

```text
film timecode
project numbering
frame / cut labels
production metadata
studio coordinate language
single restrained accent
```

Contoh:

```text
PROJECT / 0048
CUT / V03
FRAME / 012
YEAR / 2026
LOCATION / BANDUNG
RUNTIME / 08:42
```

Technical decoration hanya dipakai jika relevan. Jangan menjadikan semua section seperti software engineering interface.

---

# 6. Color Philosophy

Color system:

```text
NEAR BLACK
+
OFF WHITE
+
MUTED GREYS
+
ONE SIGNATURE ACCENT
```

## 6.1 Baseline Palette

```text
BDJG BLACK       #060607
SURFACE CARD     #101013
SURFACE ELEV     #17171B
BORDER DARK      rgba(255,255,255,.09)

OFF WHITE        #F5F6F8
MUTED GREY       #9BA0AE
```

## 6.2 Signature Accent v1

```text
BDJG ACCENT      #F97316   (orange)
ACCENT STRONG    #EA580C
HIGHLIGHT        #FFC700   (yellow)
```

Accent hanya digunakan untuk:

```text
primary CTA
focus
active state
selected state
progress
notification
important data point
```

Accent **tidak** digunakan menjadi background besar di setiap section.

## 6.3 Portal Status Color Set

Dashboard memakai satu set warna semantik untuk status chip:

```text
info / proses      #1B4FD8
warning / draft    #F59E0B
accent             #F97316
highlight          #FFC700
success            #16A34A
danger             #E11D48
internal / review  #7C3AED
client-facing      #0D9488
neutral / archived #94A3B8
owner / premium    #000000 + #FFC700 text
```

Chip selalu translucent-tint background + teks terang + label teks.
Tidak ada warna status tanpa label.

---

# 7. Semantic Color Tokens

Design guidance harus memakai semantic role:

```text
surface.canvas
surface.primary
surface.secondary
surface.elevated

text.primary
text.secondary
text.muted
text.inverse

border.subtle
border.default
border.strong

accent.primary
accent.hover
accent.active

status.success
status.warning
status.error
status.info
```

Status selalu menggunakan:

```text
icon + label + color
```

Bukan warna saja.

---

# 8. Typography Strategy

BDJG menggunakan dua karakter tipografi:

```text
DISPLAY / EDITORIAL
+
MONO / TECHNICAL
```

## 8.1 Display Font

Digunakan untuk:

```text
hero
section title
project title
large statement
large CTA
```

Karakter:

```text
clean grotesk / geometric sans
strong at large scale
modern
high readability
```

Contoh arah keluarga font:

```text
Inter Display
Manrope
Satoshi
General Sans
Neue Montreal-like grotesk
```

Tidak mengikat pada satu font tertentu.

## 8.2 Mono Font

Digunakan untuk:

```text
metadata
project number
year
timecode
category
technical label
micro navigation
```

Contoh:

```text
JetBrains Mono
IBM Plex Mono
Space Mono
```

**JetBrains Mono bukan display font utama.**

## 8.3 Selected Stack

Public Website:

```text
DISPLAY = grotesk dari arah 8.1
LABEL/META = mono dari arah 8.2
```

Portal / Dashboard (baseline hitam-dashboard):

```text
DISPLAY + HEADING + ANGKA METRIK + TIMECODE = Sora (700/800)
UI BODY + FORM + TABEL = Inter (500/600/700)
```

Label teknis di portal memakai Inter/Sora uppercase + letter-spacing,
bukan mono. Mono tetap dipakai di Public Website.

---

# 9. Typography Scale

Desktop baseline:

```text
DISPLAY XXL
clamp(96px, 13vw, 220px)

DISPLAY XL
clamp(72px, 10vw, 168px)

DISPLAY L
clamp(56px, 7vw, 120px)

H1
64–88px

H2
48–64px

H3
32–44px

H4
24–32px

BODY LARGE
20–24px

BODY
16–18px

BODY SMALL
14–15px

LABEL
11–13px

MICRO
9–11px
```

Display text:

```text
tight line-height
short line length
strong scale contrast
```

Body text:

```text
comfortable line-height
controlled max-width
```

Label:

```text
UPPERCASE
small
mono
wide letter-spacing
```

---

# 10. Signature Type Contrast

Salah satu karakter BDJG:

```text
VERY LARGE DISPLAY
+
VERY SMALL TECHNICAL LABEL
```

Contoh:

```text
WE CAPTURE
STORIES.

BDJG® / VISUAL STUDIO
BANDUNG — INDONESIA / 2026
```

---

# 11. Grid System

## Public Desktop

```text
12-column fluid grid
max content width ≈ 1600px
large outer margins
fluid gutters
```

Visual boleh melanggar grid secara terkontrol:

```text
media overlap
offset title
cross-column image
free floating frame
```

Tetapi grid tetap menjadi fondasi.

## Portal

```text
12-column functional grid
stable content width
consistent gutters
sidebar + content
```

Baseline dashboard (hitam-dashboard):

```text
SIDEBAR      268px fixed
TOPBAR       sticky, blur, border bawah
CONTENT      max-width 1460px, padding 22/26
GRIDS        4-up stats · 2fr+1fr · 3-up · 2-up
GAP          14–16px
```

Portal tidak menggunakan overlap dekoratif berlebihan.

---

# 12. Spacing

Core scale:

```text
4
8
12
16
20
24
32
40
48
64
80
96
120
160
200
```

Public Website cenderung menggunakan spacing besar.

Client:

```text
16–64
```

Admin / Worker:

```text
8–32
```

Whitespace diperlakukan sebagai bagian desain, bukan ruang yang harus selalu diisi.

---

# 13. Shape Language

## Functional UI

Gunakan:

```text
8–18px radius
clear rectangles
subtle pills
```

Untuk:

```text
input
table
modal
dashboard cards
task card
filters
```

## Public Website

Boleh menggunakan:

```text
large curved surfaces
organic clipping
large-radius blocks
asymmetrical image crops
floating rectangular frames
```

---

# 14. Curved Blocks

Curved block menjadi primitive visual Public Website.

Digunakan untuk:

```text
section transition
services
testimonial
featured project
large CTA
visual break
```

Jangan gunakan curved blocks pada:

```text
admin tables
finance rows
task lists
invoice data
form fields
```

---

# 15. Image Treatment

Foto harus:

- high resolution;
- terlihat natural;
- tidak tertutup UI dekoratif berat;
- mempertahankan tone proyek;
- tidak diberi satu filter yang memaksa semua karya terlihat sama.

Aspect ratio bebas sesuai editorial composition:

```text
portrait
landscape
square
wide cinematic
full bleed
free floating
```

Public portfolio tidak perlu menyamakan semua project ke rasio yang sama.

---

# 16. Video Treatment

Video adalah media inti BDJG.

Penggunaan:

```text
hero fragment
hover preview
full-width film showcase
project trailer
atmospheric loop
client review player
```

Public autoplay:

```text
muted
short loop
no intrusive control
deliberate poster frame
```

Client review:

```text
full controls
timecode
version
comment marker
revision context
```

---

# 17. Motion Philosophy

> **Motion should feel like a film opening sequence, not a JavaScript effects demo.**

Motion harus:

```text
purposeful
smooth
cinematic
restrained
predictable
performant
```

Tidak semua elemen harus bergerak.

---

# 18. Motion Hierarchy

## Primary Motion

```text
hero reveal
major section transition
page transition
featured work reveal
```

## Secondary Motion

```text
image scale
text mask reveal
parallax
hover preview
cursor response
```

## Micro Motion

```text
button
toggle
tabs
dropdown
status update
```

---

# 19. Text Motion

Allowed:

```text
mask reveal
clip reveal
line reveal
stagger
subtle translate
opacity fade
```

Avoid:

```text
random rotation
letter shaking
bounce
continuous blur
distortion that hurts readability
```

---

# 20. Scroll Behaviour

Public Website dapat menggunakan:

```text
sticky section
subtle parallax
layered media scroll
horizontal work strip
image displacement
curved section entrance
```

Tidak boleh:

```text
aggressive scroll hijack
long forced animation
content inaccessible without animation
```

---

# 21. Cursor System

Custom cursor hanya desktop pointer device.

States:

```text
DEFAULT
VIEW
PLAY
DRAG
NEXT
OPEN
```

Contoh:

```text
[ VIEW ]
[ PLAY ]
[ DRAG ]
```

Pada touch device:

```text
custom cursor disabled
```

Custom cursor tidak boleh merusak accessibility pointer standar.

---

# 22. Page Transition

Public:

```text
fade + mask
frame wipe
black overlay
thumbnail expansion
```

Portal:

```text
normal product transition
minimal fade
```

Reduced-motion:

```text
complex transition → simple fade / none
```

---

# 23. Public Navigation

Desktop:

```text
BDJG®

WORKS
SERVICES
ABOUT
STUDIO
CONTACT

BOOK A PROJECT ↗
```

Header states:

```text
hero
scrolled
hover
active
menu-open
```

Hero:

```text
minimal / transparent
```

Scrolled:

```text
higher contrast
slightly compact
```

---

# 24. Mobile Navigation

```text
BDJG
MENU
```

Overlay:

```text
WORKS
SERVICES
ABOUT
STUDIO
CONTACT

BOOK A PROJECT
```

Menu typography boleh besar.

---

# 25. Hero System

Hero adalah signature terbesar Public Website.

Harus terasa:

```text
large
layered
cinematic
editorial
interactive
```

Baseline composition:

```text
BDJG®                                      BOOK PROJECT ↗


                    WE CAPTURE

          [MEDIA]              [MEDIA]

                     STORIES.

                               [MEDIA]


PHOTO — FILM — VFX — POST
BANDUNG / INDONESIA
```

---

# 26. Hero Media Behaviour

Media dapat:

```text
overlap typography
cross grid
slightly rotate
move subtly with pointer
shift on scroll
```

Suggested rotation:

```text
-6° to +6°
```

Jangan berubah menjadi scrapbook chaos.

---

# 27. Hero Anti-Pattern

Jangan gunakan hero generik:

```text
Centered headline
Subtitle
CTA
Dashboard screenshot
```

BDJG bukan SaaS.

---

# 28. Intro / Preloader

Optional.

Jika digunakan:

```text
BDJG
00:00:00:00
LOADING VISUALS
```

Preloader harus singkat dan tidak menjadi gimmick yang memperlambat user.

---

# 29. Selected Works

Selected Works diperlakukan sebagai **hero kedua**.

Karakter:

```text
large media
asymmetrical
different ratios
large title
small metadata
controlled offset
```

Avoid:

```text
[card][card][card]
[card][card][card]
```

sebagai satu-satunya pola portfolio.

---

# 30. Work Card

Normal:

```text
MEDIA
PROJECT TITLE
CATEGORY / YEAR
```

Hover:

```text
still image → muted video
media slight scale
metadata shift
cursor → VIEW
```

---

# 31. Portfolio Composition

Allowed:

```text
1 large + 1 small
portrait beside landscape
offset frame
full-width project
alternating alignment
different media ratios
```

Avoid:

```text
identical cards
same ratio everywhere
heavy card borders
overly symmetrical repetition
```

---

# 32. Project Detail

Structure:

```text
Project Hero
Metadata
Film / Gallery
Story
Scope
Credits
Selected Frames
Behind The Scene
Related Work
Next Project
```

Metadata:

```text
PROJECT / 0048
TYPE / WEDDING FILM
YEAR / 2026
LOCATION / BANDUNG
RUNTIME / 08:42
```

---

# 33. Studio Statement

Large statement example:

```text
WE DON'T JUST
CAPTURE MOMENTS.

WE TURN THEM
INTO STORIES.
```

Gunakan whitespace besar.

Tidak perlu dibungkus banyak card.

---

# 34. Services

Baseline:

```text
01  PHOTOGRAPHY
02  FILM PRODUCTION
03  POST PRODUCTION
04  VFX & ANIMATION
05  CREATIVE PROJECT
```

Interaction dapat:

```text
reveal visual
shift title
show metadata
subtle accent state
```

Service tidak harus selalu ditampilkan sebagai boxed cards.

---

# 35. Process Section

Public process:

```text
01 BRIEF
02 PRE-PRODUCTION
03 SHOOT
04 POST
05 REVIEW
06 DELIVERY
```

Tujuan: menjelaskan perjalanan client secara sederhana.

Bukan menggambarkan seluruh internal workflow.

---

# 36. CTA System

Primary CTA:

```text
BOOK A PROJECT ↗
```

Alternative:

```text
START A PROJECT ↗
VIEW WORK ↗
WATCH FILM ▶
```

Primary CTA harus jelas dari halaman ke halaman.

---

# 37. Buttons

## Primary

```text
accent surface
high contrast
strong label
```

## Secondary

```text
transparent
border
```

## Text Link

```text
label + arrow
```

## Portal / Dashboard Button Grammar

```text
PRIMARY   = black surface + border terang (aksi normal operasional)
ACCENT    =orange gradient (CTA konversi: Inquiry, Quotation, Pay-flow utama)
SUCCESS   = green gradient (konfirmasi uang masuk: Bayar, Approve)
GHOST     = translucent putih (aksi sekunder,filter,kecil)
DANGER    = red tint (Reject, Suspend, Void)
```

Accent orange di portal adalah tombol aksi konversi,
bukan satu-satunya "primary" untuk semua tombol.

States:

```text
default
hover
focus-visible
active
loading
disabled
```

---

# 38. Footer

Public footer boleh expressive:

```text
HAVE A STORY
TO TELL?

BOOK A PROJECT ↗

BDJG®
PHOTO / FILM / VFX / POST

INSTAGRAM
YOUTUBE
VIMEO
EMAIL

BANDUNG — INDONESIA
© BDJG
```

---

# 39. Public Forms

Book Project dapat memakai multi-step:

```text
01 SERVICE
02 PACKAGE
03 BRIEF
04 DATE
05 CONTACT
06 REVIEW
```

Form style:

```text
large labels
clean inputs
minimal borders
clear error
clear progress
```

Field states:

```text
default
hover
focus
filled
error
disabled
success
```

---

# 40. Public Homepage Master Flow

```text
01 INTRO / PRELOADER
02 HERO
03 SELECTED WORKS
04 STUDIO STATEMENT
05 CAPABILITIES
06 FEATURED FILM
07 SERVICES
08 PROCESS
09 STUDIO / PEOPLE
10 SELECTED CLIENTS
11 CTA
12 FOOTER
```

---

# 41. Homepage Visual Rhythm

Alternation:

```text
HIGH IMPACT
↓
BREATHING SPACE
↓
MEDIA
↓
TEXT
↓
MEDIA
↓
CURVED TRANSITION
↓
INFORMATION
↓
CTA
```

Jangan membuat seluruh halaman mempunyai intensity yang sama.

---

# 42. Client Portal Direction

Visual ratio:

```text
40% BRAND
60% PRODUCT UI
```

Karakter:

```text
premium
dark
clean
calm
structured
clear action hierarchy
```

Tidak menggunakan experimental layout pada task penting.

---

# 43. Client Sidebar

```text
BDJG

Dashboard
My Projects
Quotations
Invoices & Payments
Schedule
Preview & Revision
Files
Messages
Notifications

Profile
Help
Logout
```

Sidebar harus stabil dan mudah dipahami.

---

# 44. Client Dashboard

Priority:

```text
What is happening?
What do I need to do?
Do I need to pay?
Is there something to review?
When is the next event?
```

Widgets:

```text
ACTIVE PROJECTS
WAITING REVIEW
PAYMENT DUE
NEXT SHOOT
RECENT FILES
```

---

# 45. Client Project Card

Data:

```text
Project ID
Project Name
Service
Status
Progress
Next Activity
Payment State
```

Visual:

```text
project cover
clean metadata
clear progress
one obvious CTA
```

---

# 46. Project Progress

Preferred:

```text
PRE-PRODUCTION    DONE
PRODUCTION        DONE
POST              ACTIVE
CLIENT REVIEW     UPCOMING
FINAL             UPCOMING
```

Status menggunakan text + visual indicator.

---

# 47. Client Preview Player

```text
┌──────────────────────────────────┐
│                                  │
│             VIDEO                │
│                                  │
└──────────────────────────────────┘

00:00:00 ───────────────── 06:42:18

V3 / CLIENT PREVIEW

COMMENTS
02:13  Replace this shot
03:41  Scene too dark
05:02  Name needs correction
```

Timecode menggunakan mono typography.

---

# 48. Revision UI

Revision card:

```text
REVISION #02

STATUS
IN PROGRESS

ITEMS
2 / 4 RESOLVED
```

Revision item:

```text
00:02:13
Replace drone shot
```

Client-facing language harus mudah dipahami.

---

# 49. Photo Selection

Gunakan editorial / masonry gallery.

States:

```text
favorite
selected
commented
```

Counter:

```text
38 / 50 SELECTED
```

Selection status harus selalu terlihat.

---

# 50. Invoice UI

Prioritas:

```text
amount
status
due date
primary action
```

Example:

```text
FINAL PAYMENT
Rp7.500.000

DUE / 04 SEP 2026

[ PAY NOW ]
```

---

# 51. Worker Workspace Direction

Visual ratio:

```text
20% BRAND
80% FUNCTIONAL
```

Priority:

```text
task
deadline
schedule
revision
files
```

Tidak menggunakan video background, custom cursor, atau hero animasi.

---

# 52. Worker Dashboard

Summary:

```text
TODAY TASKS
OVERDUE
UPCOMING SHOOT
REVISION
INTERNAL REVIEW
```

Worker harus tahu apa yang harus dilakukan tanpa membuka banyak halaman.

---

# 53. Worker Project View

Tabs:

```text
Overview
Brief
Schedule
Team
My Tasks
Files
Internal Notes
Feedback
```

Project media hanya menjadi identity anchor.

---

# 54. Task Card

```text
TITLE
PROJECT
DUE
PRIORITY
STATUS
ASSIGNEE
```

Status color digunakan minimal.

---

# 55. Admin / Owner Direction

Visual ratio:

```text
10% BRAND
90% OPERATIONAL
```

Admin harus terasa seperti:

```text
STUDIO CONTROL ROOM
```

Bukan portfolio page dengan chart.

---

# 56. Admin Sidebar

```text
Dashboard

Sales
Projects
Team
Finance
Content
Communication
System
```

Nested group dapat collapse.

Active state harus jelas.

---

# 57. Admin Dashboard

Order:

```text
Summary Metrics
Attention Queue
Today Schedule
Production Pipeline
Finance Snapshot
Recent Activity
```

Visual hierarchy:

```text
ATTENTION
>
STATUS
>
TREND
>
DECORATION
```

---

# 58. Metric Cards

Example:

```text
ACTIVE PROJECTS
12

+2 THIS MONTH
```

Avoid:

```text
random gradient
neon glow
heavy shadow
large illustration
```

Baseline dashboard mengizinkan pola terkontrol ini:

```text
ikon kartu = tile gradient kecil (maks ±46px)
shadow kartu = elevation lembut, bukan glow
hover = translateY kecil
```

Gradient tidak boleh menjadi latar seluruh card.

---

# 59. Finance Dashboard

Preferred chart:

```text
line
bar
stacked bar
simple donut when appropriate
```

Avoid:

```text
3D chart
decorative gauge
rainbow palette
```

One primary series can use accent.

Secondary series remain neutral.

---

# 60. Tables

Table rules:

```text
clear header
subtle row divider
hover state
sorting indicator
filter state
row actions
empty state
pagination
```

Sticky header where useful.

---

# 61. Production Board / Kanban

Columns:

```text
PRE-PRODUCTION
PRODUCTION
POST
CLIENT REVIEW
REVISION
FINAL
```

Project card:

```text
Project
Client
Deadline
Worker
Payment alert
Revision alert
```

Must remain scannable.

---

# 62. Calendar

Modes:

```text
Month
Week
Agenda
```

Event categories dapat menggunakan muted color system.

Jangan overload warna.

---

# 63. Notifications

Item:

```text
icon
title
short description
time
related project
read state
```

Important:

```text
accent dot
or accent border
```

---

# 64. Messages

Client-facing message UI:

```text
clean
professional
project-context aware
attachment capable
```

Internal note harus visual berbeda dari client thread.

---

# 65. Modal & Drawer

Modal:

```text
confirmation
short focused action
critical decision
```

Drawer:

```text
filters
quick detail
activity
project snapshot
```

Jangan stack modal di atas modal.

---

# 66. Empty States

Example:

```text
NO ACTIVE PROJECTS

Your active projects will appear here.
```

Optional CTA.

Avoid cartoon illustration berlebihan.

---

# 67. Loading States

Public:

```text
media skeleton
progressive image reveal
minimal branded loader
```

Portal:

```text
skeleton rows
skeleton cards
inline progress
```

Avoid full-screen spinner untuk setiap operasi rutin.

---

# 68. Error States

Harus menjelaskan:

```text
what happened
what user can do
```

Example:

```text
UPLOAD FAILED

The file could not be uploaded.
Try again or choose another file.
```

---

# 69. Success States

Example:

```text
PAYMENT RECEIVED

Your project payment has been confirmed.
```

Tidak perlu confetti pada area internal.

---

# 70. Component States

Semua interactive component harus mempertimbangkan:

```text
default
hover
focus-visible
active
selected
disabled
loading
error
success
```

Tidak semua state harus selalu tampil, tapi state yang relevan wajib terdefinisi.

---

# 71. Focus States

Keyboard focus selalu terlihat.

Preferred:

```text
2–3px equivalent accent outline
high contrast
clear separation
```

Jangan menghapus focus tanpa replacement.

---

# 72. Accessibility Baseline

Target:

```text
WCAG 2.2 AA
```

Design requirements:

- keyboard operable;
- focus-visible;
- sufficient contrast;
- semantic heading hierarchy;
- form labels;
- error association;
- no essential information via color only;
- captions/transcripts where relevant;
- media interactions accessible.

---

# 73. Reduced Motion

Saat reduced motion aktif:

```text
disable parallax
disable custom cursor effects
disable large image displacement
replace cinematic transition with simple fade
avoid auto scroll effects
```

Konten tetap harus lengkap.

---

# 74. Responsive Breakpoint Concept

```text
Mobile
< 640

Tablet
640–1023

Desktop
1024–1439

Wide
1440+
```

Nilai final boleh beradaptasi.

---

# 75. Desktop Behaviour

Public Desktop adalah surface paling expressive.

Allowed:

```text
overlap
large display
hover preview
custom cursor
asymmetrical composition
scroll effects
```

---

# 76. Tablet Behaviour

Kurangi:

```text
overlap intensity
rotation
cursor dependency
extreme spacing
```

Pertahankan:

```text
strong media
editorial hierarchy
large type
```

---

# 77. Mobile Behaviour

Mobile **bukan desktop yang dikecilkan**.

Harus:

```text
stack intentionally
reduce overlap
remove cursor dependency
simplify parallax
keep large type
keep media dominant
```

---

# 78. Mobile Hero

Example:

```text
BDJG®

WE
CAPTURE
STORIES.

[MEDIA]

PHOTO / FILM / VFX
BANDUNG / ID
```

Floating media tidak boleh menutupi headline.

---

# 79. Mobile Portfolio

Preferred:

```text
single-column editorial
alternating ratio
large title
small metadata
tap-based preview / open
```

Hover-only interactions harus memiliki tap equivalent.

---

# 80. Portal Mobile

Client:

```text
compact drawer or bottom navigation
stacked cards
priority actions stay visible
```

Tables:

```text
desktop table → mobile data cards where needed
```

Admin mobile fokus monitoring, bukan memaksa semua operasi kompleks menjadi mobile-first.

---

# 81. Content Voice

BDJG copy:

```text
short
confident
direct
visual
non-corporate
```

Prefer:

```text
WE CAPTURE STORIES.
SELECTED WORKS
START A PROJECT
CLIENT PREVIEW
FINAL DELIVERY
```

Avoid:

```text
We are delighted to provide comprehensive innovative multimedia solutions...
```

---

# 82. Production Language

Public technical language dapat dipakai sebagai aksen:

```text
PROJECT / 0048
CUT / V03
FRAME / 012
YEAR / 2026
LOCATION / BANDUNG
```

Jangan overuse.

---

# 83. Iconography

Icon style:

```text
simple
consistent
minimal
restrained
```

Gunakan satu keluarga ikon.

Emoji pada `hitam-dashboard.html` adalah placeholder development;
implementasi produksi memakai satu set ikon linear yang konsisten.

---

# 84. Illustration

Priority:

```text
real portfolio media
production stills
film frames
photography
```

Illustration hanya secondary.

---

# 85. Privacy Labels

Private content harus memiliki label:

```text
INTERNAL
CLIENT PREVIEW
CLIENT SHARED
FINAL
PRIVATE
```

Jangan mengandalkan URL tersembunyi untuk memberi rasa aman secara visual.

---

# 86. Version Labels

Compact labels:

```text
V1
V2
V3
FINAL
```

Gunakan mono type.

---

# 87. Attention System

Levels:

```text
INFO
NEEDS ACTION
URGENT
BLOCKED
OVERDUE
```

Admin harus memprioritaskan:

```text
NEEDS ACTION
URGENT
BLOCKED
OVERDUE
```

---

# 88. Destructive Actions

Examples:

```text
Delete project
Void invoice
Refund payment
Remove worker
Disable user
```

Visual requirements:

```text
destructive color
clear label
confirmation
object context
```

Bad:

```text
Are you sure?
```

Good:

```text
VOID INVOICE BDJG-INV-0048?

This invoice will no longer accept payment.
```

---

# 89. Anti-Patterns — Public

Never:

- 3 identical cards per row across the whole site;
- every section as SaaS rounded card;
- glassmorphism everywhere;
- neon gradients everywhere;
- dashboard screenshot in hero;
- autoplay audio;
- every element animated;
- heavy scroll hijacking;
- excessive blur;
- generic stock photography when actual work exists;
- overly symmetric compositions throughout.

---

# 90. Anti-Patterns — Client Portal

Never:

- giant display typography for operational labels;
- hide actions for aesthetic reasons;
- cinematic transition for routine action;
- ambiguous icon-only controls;
- mix internal and client notes;
- show data client does not need.

---

# 91. Anti-Patterns — Worker / Admin

Never:

```text
custom cursor
full-screen video
animated hero
large experimental typography
floating portfolio imagery
parallax dashboard
```

Internal surfaces are tools.

---

# 92. Public Homepage Section Intent

## Intro
Set identity quickly.

## Hero
Create immediate emotional impact.

## Selected Works
Prove quality.

## Statement
Express philosophy.

## Capabilities
Explain what BDJG can do.

## Featured Film
Show immersive proof.

## Services
Translate capability into service.

## Process
Reduce uncertainty.

## Studio
Build trust.

## Clients
Add social proof.

## CTA
Convert.

---

# 93. Page Height & Rhythm

Section may be:

```text
70vh
100vh
120vh+
```

depending on storytelling.

Do not force every section into 100vh.

---

# 94. Public Project Transition

Preferred:

```text
click project
→ media expands
→ title transitions
→ project detail
```

Fallback:

```text
fast cut / fade
```

---

# 95. Client Master Layout

```text
┌──────────────┬───────────────────────────────┐
│ SIDEBAR      │ PAGE HEADER                   │
│              │                               │
│              │ CONTENT                       │
│              │                               │
└──────────────┴───────────────────────────────┘
```

---

# 96. Admin Master Layout

```text
┌──────────────┬────────────────────────────────┐
│ SIDEBAR      │ HEADER / SEARCH / ACTIONS      │
│              ├────────────────────────────────┤
│              │ FILTER / TOOLBAR               │
│              ├────────────────────────────────┤
│              │ TABLE / BOARD / DATA / CHART   │
└──────────────┴────────────────────────────────┘
```

---

# 97. Page Header Pattern

Portal:

```text
EYEBROW
PAGE TITLE
SHORT DESCRIPTION

[PRIMARY ACTION]
```

Example:

```text
PROJECTS
All Projects

Manage active and completed projects.

[ NEW PROJECT ]
```

---

# 98. Breadcrumbs

Use for deep routes:

```text
Projects / BDJG-0048 / Revisions / Revision 02
```

---

# 99. Search & Filter Visual Pattern

Admin search targets:

```text
Project
Client
Invoice
Worker
Quotation
```

Filters should be:

```text
easy to scan
removable
visible when active
```

---

# 100. Data Density

Recommended:

```text
Client → Comfortable
Worker → Medium
Admin → Compact / Comfortable toggle
```

---

# 101. Avatar Rules

Use avatar where identity is helpful:

```text
assignee
comment author
client
reviewer
```

Do not add avatars as decoration everywhere.

---

# 102. Badge Rules

Badges for:

```text
status
priority
visibility
version
category
```

Avoid badge overload.

---

# 103. Tooltip Rules

Use for:

```text
unfamiliar icon
technical abbreviation
truncated value
chart detail
```

Core information should not depend on tooltip.

---

# 104. Performance-Aware Visual Design

Avoid patterns that inherently create unnecessary load:

```text
10 simultaneous autoplay videos
full-resolution media everywhere
constant 3D transforms
multiple blurred glass layers
continuous heavy animation
```

Media experience should feel premium, not heavy.

---

# 105. Brand Consistency

All surfaces share:

```text
logo
font family
accent
icon family
status grammar
button grammar
labels
```

They differ in:

```text
scale
motion
density
composition
```

---

# 106. Public → Client Transition

When user enters portal:

```text
visual intensity decreases
navigation becomes functional
motion reduces
media becomes contextual
brand remains recognizable
```

---

# 107. Public Design Checklist

```text
Does this feel cinematic?
Is media dominant?
Is typography confident?
Is the composition too generic?
Is motion purposeful?
Is CTA obvious?
Is whitespace intentional?
Does it work without motion?
Does mobile feel intentional?
```

---

# 108. Client Portal Checklist

```text
Can client immediately see project status?
Can client see the next required action?
Can client find payment?
Can client review preview?
Can client submit revision?
Can client find final files?
Are internal-only elements hidden?
```

---

# 109. Worker Checklist

```text
Can worker see today's tasks?
Can worker see deadlines?
Can worker see only assigned projects?
Can worker find required files?
Can worker submit internal review?
Can worker find revision items?
```

---

# 110. Admin Checklist

```text
Can admin see what needs attention?
Can admin search quickly?
Can admin filter projects?
Can admin see schedule conflict?
Can admin see payment problems?
Can admin distinguish internal vs client data?
Can admin understand status without opening every record?
```

---

# 111. Visual QA Checklist

Review:

```text
Typography
Line length
Contrast
Spacing
Alignment
Grid
Media crop
Image quality
Video poster
Hover
Focus
Loading
Empty
Error
Disabled
Selected
Responsive
Reduced motion
Status clarity
CTA hierarchy
```

---

# 112. Design Review Score

Score 1–5:

```text
Brand Fit
Hierarchy
Clarity
Media Quality
Motion Quality
Accessibility
Responsive Quality
Operational Efficiency
```

Public weights more heavily:

```text
Brand Fit
Media Quality
Motion Quality
```

Portal weights more heavily:

```text
Clarity
Accessibility
Operational Efficiency
```

---

# 113. Final Public DNA

```text
DARK
EDITORIAL
CINEMATIC
ASYMMETRICAL
MEDIA-LED
BIG TYPE
SMALL META
CURVED BLOCKS
CONTROLLED MOTION
PREMIUM
```

---

# 114. Final Client DNA

```text
PREMIUM
CALM
CLEAR
PROJECT-FIRST
ACTION-LED
CONTROLLED
```

---

# 115. Final Worker DNA

```text
FOCUSED
TASK-FIRST
DEADLINE-FIRST
LOW-NOISE
FAST
```

---

# 116. Final Admin DNA

```text
ATTENTION-FIRST
DATA-FIRST
OPERATIONAL
DENSE
CONTROLLED
CLEAR
```

---

# 117. Master Design Hierarchy

```text
BDJG
│
├── PUBLIC
│   ├── cinematic
│   ├── editorial
│   ├── portfolio-first
│   └── motion-led
│
├── CLIENT
│   ├── premium
│   ├── calm
│   ├── project-first
│   └── action-led
│
├── WORKER
│   ├── task-first
│   ├── deadline-first
│   └── efficient
│
└── ADMIN / OWNER
    ├── attention-first
    ├── data-first
    ├── operational
    └── controlled
```

---

# 118. Relationship with `blueprint.md`

`blueprint.md` owns:

```text
roles
permissions
business flow
project lifecycle
data relationship
system modules
```

`DESIGN-v1.md` owns:

```text
visual language
layout
typography
color
motion
interaction
responsive rules
component grammar
design differentiation
design QA
```

Design must support the system, not hide it.

---

# 119. Baseline Decision

BDJG menggunakan **energi** dari premium creative-studio references seperti Mondragon Demo 6 tanpa menjadi clone.

Signature BDJG:

```text
large editorial type
film-oriented metadata
near-black surfaces
off-white text
one restrained accent
asymmetrical portfolio
curved visual vocabulary
cinematic motion
technical mono labels
strong film/photo media
```

Separation antar-surface wajib:

```text
PUBLIC = EXPERIENCE
CLIENT = TRUST
WORKER = FOCUS
ADMIN = CONTROL
```

---

# 120. Final Rule

Setiap page atau component baru harus menjawab:

```text
Which experience zone is this?

What is the user's primary goal?

What information must dominate?

What should stay quiet?

Does this actually need motion?

Does this actually need media?

Is the interaction accessible?

Does it still feel like BDJG?

Is it creative because it serves the story,
or merely because it is unusual?
```

Jika jawabannya hanya:

> “karena terlihat unik”

maka desain sebaiknya disederhanakan.

---

**END — BDJG VISUAL DESIGN SYSTEM v1.0.0**
