# Prevent Copy Plugin for Moodle

The **Prevent Copy** plugin protects your course content by preventing students and other users from copying, cutting, or pasting text on specific pages. This is useful when you want to protect exam content, sensitive materials, or prevent unauthorized copying of course resources.

## What This Plugin Does

Once activated, this plugin can:
- Block right-click context menus on protected pages
- Prevent copy, cut, and paste operations
- Block text selection on specific course pages
- Apply protections to specific user roles (students, teachers, etc.)

## Installation

1. Download the plugin files to your Moodle server
2. Extract them into the `/local/preventcopy/` directory
3. Go to **Site Administration > Notifications** to complete the installation
4. The plugin will be automatically enabled

## Setting Up the Plugin

### Step 1: Enable the Plugin for Your Users

Go to **Site Administration > Plugins > Local plugins > Prevent Copy**

You'll see these settings:

**Which users should have copy/paste disabled?**
- Check "**Students**" to protect content from students
- Check "**Teachers & Staff**" to also protect content from teachers, managers, and other staff members
- Note: Site administrators are never restricted

### Step 2: Choose What to Protect

The plugin supports protection at multiple levels:


- Course categories
- Individual courses
- Activity/resource types
- Specific activities/resources using Course Module IDs (CMIDs)



---

### Course Category Protection

**Course Category IDs**
- Enter Moodle course category IDs separated by commas
- Use `*` to protect all categories

Example:
```text
1,3,7
```

---

### Course Protection

**Course IDs**
- Enter Moodle course IDs separated by commas
- Use `*` to protect all courses

Example:
```text
2,5,10
```

---

### Activity Protection

Each activity/resource setting accepts:

- Specific Course Module IDs (CMIDs)
- Multiple CMIDs separated by commas
- `*` to protect all activities/resources of that type

#### Quiz
```text
*
```
Protect all quizzes

```text
12,15,20
```
Protect only specific quizzes

---

#### Assignment
```text
*
```
Protect all assignments

```text
8,11
```
Protect only specific assignments

---

#### Lesson
```text
*
```
Protect all lessons

```text
21,25
```
Protect only specific lessons

---

### Resource Protection

#### Page
```text
*
```
Protect all page resources

```text
30,35
```
Protect only specific pages

---

#### Book
```text
*
```
Protect all book resources

```text
40,45
```
Protect only specific books

---

### Common Module IDs

**Common Module ID**
- Protect specific activities/resources regardless of type
- Accepts CMIDs separated by commas
- Use `*` to protect all modules

Example:
```text
50,55,60
```

---

### How to Find a CMID

Open the activity/resource in Moodle and check the URL.

Example:
```text
http://sitedomain.com/mod/quiz/view.php?id=25
```

Here:
```text
25
```

is the Course Module ID (CMID).

---

### Priority Order

Protection is applied in this order:

1. Course Category
2. Course
3. Activity/Resource Type
4. Common Module IDs
### Step 3: Configure What Actions to Block

In the "**Script Configuration**" field, you'll see code that controls what users cannot do. The default code blocks:
- Right-click context menu
- Copy (`Ctrl+C` / `Cmd+C`)
- Cut (`Ctrl+X` / `Cmd+X`)
- Paste (`Ctrl+V` / `Cmd+V`)
- Text selection

## Customizing JavaScript Restrictions

### Understanding the Script Configuration

The plugin uses JavaScript code to block user actions. The default configuration looks like this:

```javascript
// Block right-click menu
document.addEventListener('contextmenu', function (e) { e.preventDefault(); });

// Block copy action (Ctrl+C)
document.addEventListener('copy', function (e) { e.preventDefault(); });

// Block paste action (Ctrl+V)
document.addEventListener('paste', function (e) { e.preventDefault(); });

// Block cut action (Ctrl+X)
document.addEventListener('cut', function (e) { e.preventDefault(); });

// Block text selection
document.addEventListener('selectstart', function (e) { e.preventDefault(); });
document.addEventListener('selectall', function (e) { e.preventDefault(); });
```

### Customizing Your Restrictions

You can modify which actions are blocked by editing the script. Here are some examples:

**Example 1: Block only right-click (allow copy/paste)**
```javascript
document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
```

**Example 2: Block copy and paste (allow right-click)**
```javascript
document.addEventListener('copy', function (e) { e.preventDefault(); });
document.addEventListener('paste', function (e) { e.preventDefault(); });
document.addEventListener('cut', function (e) { e.preventDefault(); });
```

**Example 3: Block only text selection**
```javascript
document.addEventListener('selectstart', function (e) { e.preventDefault(); });
```

**Example 4: Disable all restrictions (leave empty)**
Leave the script configuration empty or remove all code to disable the plugin without uninstalling it.

**Example 5: Complete block, event in TinyMCE editor**
This also include standard blocked events. Just copy below content
```javascript
<script>
(() => {

  const blockedEvents = [
    'copy',
    'cut',
    'paste',
    'contextmenu',
    'dragover',
    'drop'
  ];

  const blockedKeys = ['c', 'v', 'x', 'a'];

  const blockedCommands = [
    'Copy',
    'Paste',
    'Cut',
    'SelectAll'
  ];

  // Global page protection
  blockedEvents.forEach(evt => {
    document.addEventListener(evt, e => {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    }, true);
  });

  document.addEventListener('keydown', e => {

    if (
      (e.ctrlKey || e.metaKey) &&
      blockedKeys.includes(e.key.toLowerCase())
    ) {
      e.preventDefault();
      e.stopImmediatePropagation();
      return false;
    }

  }, true);

  // TinyMCE protection
  const setupEditor = editor => {

    editor.on('init', () => {

      const doc = editor.getDoc();
      const body = editor.getBody();

      if (!doc || !body) {
        return;
      }

      // Block iframe/native events
      blockedEvents.forEach(evt => {

        doc.addEventListener(evt, blockEvent, true);
        body.addEventListener(evt, blockEvent, true);

      });

      // Block keyboard shortcuts
      doc.addEventListener('keydown', blockKeys, true);
      body.addEventListener('keydown', blockKeys, true);

      // Block beforeinput (important)
      doc.addEventListener('beforeinput', e => {

        if (
          e.inputType &&
          (
            e.inputType.includes('paste') ||
            e.inputType.includes('insertFromPaste')
          )
        ) {
          blockEvent(e);
        }

      }, true);

      // TinyMCE internal commands
      editor.on('ExecCommand', e => {

        if (
          blockedCommands.includes(e.command)
        ) {
          e.preventDefault();
          return false;
        }

      });

      // TinyMCE paste pipeline
      editor.on(
        'Paste PrePaste postpaste paste Copy Cut BeforeExecCommand',
        e => {
          e.preventDefault();
          e.stopPropagation();
          return false;
        }
      );

    });

  };

  // Generic event blocker
  const blockEvent = e => {

    e.preventDefault();

    e.stopPropagation();

    e.stopImmediatePropagation();

    return false;

  };

  // Keyboard blocker
  const blockKeys = e => {

    if (
      (e.ctrlKey || e.metaKey) &&
      blockedKeys.includes(e.key.toLowerCase())
    ) {

      e.preventDefault();

      e.stopPropagation();

      e.stopImmediatePropagation();

      return false;
    }

  };

  // Wait for TinyMCE
  const init = () => {

    if (typeof tinymce === 'undefined') {
      return setTimeout(init, 500);
    }

    // Existing editors
    if (tinymce.editors && tinymce.editors.length) {
      tinymce.editors.forEach(setupEditor);
    }

    // Future editors
    tinymce.on('AddEditor', e => {
      setupEditor(e.editor);
    });

  };

  init();

})();
</script>
```

## Common Use Cases

### Protecting Exam Content
```
Pages: /mod/quiz
Users: Students
Script: Full default (all blocks enabled)
```

### Protecting Lesson Materials
```
Pages: /mod/lesson
Users: Students
Script: Full default
```

### Protecting Sensitive Documents
```
Pages: /mod/page, course=3
Users: Students and Teachers
Script: Full default
```

### Allow Copy But Block Right-Click
```
Pages: Your protected pages
Users: Students
Script: Only the contextmenu line
```

## How Users Will Experience This

When protection is active:

- Users **cannot** see the context menu when they right-click
- Users **cannot** copy text using Ctrl+C (or Cmd+C on Mac)
- Users **cannot** cut text using Ctrl+X (or Cmd+X on Mac)
- Users **cannot** paste using Ctrl+V (or Cmd+V on Mac)
- Users **cannot** select and drag text

### What Users CAN Still Do
- Read and view all content normally
- Use keyboard navigation (Tab, Arrow keys)
- Access links by clicking on them
- Print the page (if browser allows)
- Take screenshots
- Use browser developer tools (if enabled)

## Important Notes

- **Administrative users** (site administrators) are never restricted, even if you enable protections for teachers
- The plugin only works on pages you specifically configure - other pages are unaffected
- Restrictions apply based on the user's role in the course or site
- This plugin enhances content security but should not be relied upon as the only security measure

## Troubleshooting

### Protection is not working

1. Verify the plugin is enabled for the correct user roles
2. Check that the configured Course ID, Category ID, or CMID is correct
3. Purge Moodle caches:
   ```
   Site Administration > Development > Purge caches
   ```
4. Refresh the browser page

---

### How to Find IDs

#### Course ID
Example:
```text
/course/view.php?id=5
```

#### Course Category ID
Example:
```text
/course/index.php?categoryid=3
```

#### Course Module ID (CMID)
Example:
```text
/mod/quiz/view.php?id=25
```

---

### TinyMCE editor still allows copy/paste

1. Refresh the page
2. Purge Moodle caches
3. Verify the JavaScript configuration includes TinyMCE protection


### Users are still able to copy

1. Some browser extensions or accessibility tools can override the restrictions
2. Advanced users with developer tools enabled may bypass protections
3. Try refreshing the page if JavaScript didn't load properly

### I want to disable the plugin temporarily

Leave the "List of pages to protect" field empty - the plugin will be inactive without needing to uninstall it.

## Support & Feedback

For issues or feature requests, please visit the plugin repository or contact your Moodle administrator.


