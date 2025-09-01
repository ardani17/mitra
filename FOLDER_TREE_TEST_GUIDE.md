# Folder Tree Selector Testing Guide

## Overview
This guide provides step-by-step instructions for testing the new folder tree selector feature in the Copy and Move modals of the Telegram Bot File Explorer.

## Prerequisites
1. Laravel application running
2. Access to the File Explorer at `/telegram-bot/explorer`
3. Some existing files and folders in `/storage/app/proyek`

## Test Scenarios

### 1. Test Copy with Folder Tree Selector

#### Steps:
1. Navigate to **Telegram Bot > File Explorer**
2. Click the **Copy** button (green icon) on any file or folder
3. Verify the Copy modal opens with:
   - Title: "Copy Item"
   - Selected path display showing "/"
   - Folder tree container with loading spinner

#### Expected Results:
- Folder tree loads automatically showing all available folders
- Root folder "/" is shown at the top
- Folders can be expanded/collapsed by clicking the arrow icon
- Clicking a folder selects it and updates the "Selected:" display
- Selected folder has blue background highlight

#### Test Actions:
1. Click on different folders to select them
2. Expand/collapse folders with children
3. Verify the selected path updates correctly
4. Click "Copy" to execute the operation
5. Verify the file/folder is copied to the selected destination

### 2. Test Move with Folder Tree Selector

#### Steps:
1. Click the **Move** button (yellow icon) on any file or folder
2. Verify the Move modal opens with similar layout as Copy modal
3. Check that the source folder is excluded from the tree (cannot move into itself)

#### Expected Results:
- Folder tree loads without the source folder (if moving a folder)
- Selected path display has yellow background
- All other behaviors same as Copy modal

#### Test Actions:
1. Try to move a folder and verify it doesn't appear in the tree
2. Select a destination and click "Move"
3. Verify the item is moved successfully
4. Check that the original item no longer exists in the source location

### 3. Test API Endpoint

#### Direct API Test:
```bash
# Test folder tree API endpoint
curl -X GET http://localhost/telegram-bot/folder-tree

# Test with exclude parameter
curl -X GET http://localhost/telegram-bot/folder-tree?exclude=folder-name
```

#### Expected Response:
```json
{
  "success": true,
  "folders": [
    {
      "name": "project-a",
      "path": "project-a",
      "hasChildren": true,
      "children": [
        {
          "name": "documents",
          "path": "project-a/documents",
          "hasChildren": false,
          "children": []
        }
      ]
    }
  ]
}
```

### 4. Edge Cases to Test

#### Empty Folders:
1. Create an empty folder structure
2. Test Copy/Move operations
3. Verify empty folders are shown correctly

#### Deep Nesting:
1. Create folders nested 5+ levels deep
2. Test navigation and selection
3. Verify all levels are accessible

#### Special Characters:
1. Create folders with spaces, dashes, underscores
2. Test selection and operations
3. Verify paths are handled correctly

#### Large Number of Folders:
1. Create 50+ folders
2. Test scrolling in the folder tree
3. Verify performance is acceptable

### 5. User Experience Tests

#### Visual Feedback:
- ✅ Selected folder is highlighted in blue
- ✅ Hover effect on folders (gray background)
- ✅ Expand/collapse arrows rotate correctly
- ✅ Folder icons are displayed
- ✅ Loading spinner shows while fetching data

#### Interaction:
- ✅ Click to select works smoothly
- ✅ Click arrow to expand/collapse without selecting
- ✅ Selected path updates immediately
- ✅ Cancel button closes modal
- ✅ Modal can be closed by clicking outside

### 6. Security Tests

#### Path Traversal:
1. Try to manipulate the API to access folders outside `/storage/app/proyek`
2. Verify security checks prevent unauthorized access

#### Invalid Paths:
1. Test with non-existent paths
2. Verify appropriate error messages

### 7. Browser Compatibility

Test on:
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers

## Common Issues and Solutions

### Issue: Folder tree not loading
**Solution**: Check browser console for errors, verify API endpoint is accessible

### Issue: Cannot select folders
**Solution**: Check JavaScript errors, ensure click handlers are properly attached

### Issue: Selected path not updating
**Solution**: Verify the onSelect callback is properly configured

### Issue: Folders not expanding
**Solution**: Check that folders have children and toggle function works

## Success Criteria

The folder tree selector is considered fully functional when:

1. ✅ All folders are displayed in hierarchical structure
2. ✅ Folders can be selected with visual feedback
3. ✅ Selected path is clearly displayed
4. ✅ Copy operation works with selected destination
5. ✅ Move operation works with proper exclusions
6. ✅ UI is responsive and user-friendly
7. ✅ No JavaScript errors in console
8. ✅ Security measures prevent unauthorized access
9. ✅ Performance is acceptable with many folders
10. ✅ Works across all major browsers

## Test Results Log

| Test Case | Status | Notes |
|-----------|--------|-------|
| Copy with folder tree | ⏳ | Pending test |
| Move with folder tree | ⏳ | Pending test |
| API endpoint | ⏳ | Pending test |
| Empty folders | ⏳ | Pending test |
| Deep nesting | ⏳ | Pending test |
| Special characters | ⏳ | Pending test |
| Large folder count | ⏳ | Pending test |
| Visual feedback | ⏳ | Pending test |
| Security tests | ⏳ | Pending test |
| Browser compatibility | ⏳ | Pending test |

## Conclusion

Once all tests pass successfully, the folder tree selector feature is ready for production use. This enhancement significantly improves the user experience by providing a visual, intuitive way to select destination folders for copy and move operations.