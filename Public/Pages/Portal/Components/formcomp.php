<?php

$Submit = '<button type="submit" class="btn btn-primary">Submit</button>';
$Cancel = '<button type="reset" class="btn btn-danger">Reset</button>';

function fhead($title = "", $heading = "", $faction = "")
{
    $formstart = '<div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">' . $title . '</h4>
                    <p class="text-muted mb-0">' . $heading . '</p>
                </div>
                <div class="card-body">
                    <form action=' . $faction . ' method="POST">';
    return $formstart;
}
function field($label, $type, $id, $placeholder, $value = "", $required = "required", $ftype = "")
{
    $html = '<div class="form-group">
                <label class="form-label" for="' . $id . '">' . $label . '</label>
                <input type="' . $type . '" name="' . $id . '" class="form-control" id="' . $id . '" value="' . htmlspecialchars($value) . '" placeholder="' . $placeholder . '" ' . $required . ' ' . $ftype . '>
            </div>';

    return $html;
}


function select($label, $id, $name, $options, $selectedOption = null)
{
    $html = '<div class="form-group">
                <label class="form-label" for="' . $id . '">' . $label . '</label>
                <select class="form-select" id="' . $id . '" name="' . $name . '">';

    foreach ($options as $option) {
        $selected = ($option == $selectedOption) ? 'selected' : '';
        $html .= '<option value=' . $option . ' >' . $option . '</option>';
    }

    $html .= '</select>
            </div>';

    return $html;
}
function selectMult($label, $id, $name, $options, $selectedOptions = [])
{
    // Starting the HTML for the multi-select dropdown
    $html = '<div class="form-group">
                <label class="form-label" for="' . $id . '">' . $label . '</label>
                <select class="form-select" id="' . $id . '" multiple>';

    // Adding options to the dropdown
    foreach ($options as $option) {
        $html .= '<option value="' . htmlspecialchars($option) . '">' . htmlspecialchars($option) . '</option>';
    }

    // Closing the select element and adding a visible input to display selections
    $html .= '</select>
              <input name='.$name.' type="text" id="' . $id . '_visible_input" class="form-control mt-2" readonly>
            </div>';

    // Adding the script to handle the select change event
    $html .= '<script>
                var selectedValues_' . $id . ' = [];

                document.getElementById("' . $id . '").addEventListener("change", function(e) {
                    var visibleInput = document.getElementById("' . $id . '_visible_input");
                    var select = e.target;
                    if (select.value) {
                      // Add the selected value to the array and update the input field
                      selectedValues_' . $id . '.push(select.value);
                      visibleInput.value = selectedValues_' . $id . '.join(", ");
                      
                      // Remove the selected option from the dropdown
                      select.remove(select.selectedIndex);
                    }
                });

                document.getElementById("' . $id . '_visible_input").addEventListener("click", function() {
                    var visibleInput = this;
                    var select = document.getElementById("' . $id . '");
                    var currentValues = visibleInput.value.split(", ");
                    
                    // Resetting the input field and dropdown selections
                    visibleInput.value = "";
                    selectedValues_' . $id . ' = [];
                    
                    // Add back the removed options to the dropdown
                    currentValues.forEach(function(value) {
                        var option = document.createElement("option");
                        option.value = value;
                        option.text = value;
                        select.appendChild(option);
                    });
                });
              </script>';

    return $html;
}
function generateCheckboxes($values, $name) {
    foreach ($values as $value) {
        echo '<label>';
        echo '<input type="checkbox" name="' . $name . '[]" value="' . htmlspecialchars($value) . '">';
        echo htmlspecialchars($value);
        echo '</label><br>';
    }
}
function generateDynamicCheckboxScript($branchDropdownId, $checkboxContainerId, $pagesData) {
    $script = "<script>
        const branchSelect = document.getElementById('$branchDropdownId');
        const checkboxContainer = document.getElementById('$checkboxContainerId');
        const pagesData = " . json_encode($pagesData) . ";

        // Function to update checkbox options based on selected branch
        function updateCheckboxOptions() {
            const selectedBranch = branchSelect.value;
            // Clear previous checkboxes
            checkboxContainer.innerHTML = '';
            // Populate checkboxes based on selected branch
            pagesData.forEach(page => {
                if (page.bname === selectedBranch) {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.name = 'selectedPages[]';
                    checkbox.value = page.name;
                    checkbox.id = page.name; // Optional: Assigning an ID for labels to work
                    const label = document.createElement('label');
                    label.textContent = page.name;
                    label.setAttribute('for', page.name); // Associating label with checkbox
                    checkboxContainer.appendChild(checkbox);
                    checkboxContainer.appendChild(label);
                    checkboxContainer.appendChild(document.createElement('br'));
                }
            });
        }

        // Event listener for branch selection change
        branchSelect.addEventListener('change', updateCheckboxOptions);

        // Initial call to populate checkboxes based on default selected branch
        updateCheckboxOptions();
    </script>";

    return $script;
}


function generateDynamicDropdownScript($branchDropdownId, $pageDropdownId, $pagesData) {
    $script = "<script>
        const branchSelect = document.getElementById('$branchDropdownId');
        const pageSelect = document.getElementById('$pageDropdownId');
        const pagesData = " . json_encode($pagesData) . ";

        // Function to update page options based on selected branch
        function updatePageOptions() {
            const selectedBranch = branchSelect.value;
            // Clear previous options
            pageSelect.innerHTML = '<option value=\"\">Select Page</option>';
            // Populate options based on selected branch
            pagesData.forEach(page => {
                if (page.bname === selectedBranch) {
                    const option = document.createElement('option');
                    option.value = page.name;
                    option.textContent = page.name;
                    pageSelect.appendChild(option);
                }
            });
        }

        // Event listener for branch selection change
        branchSelect.addEventListener('change', updatePageOptions);

        // Initial call to populate page options based on default selected branch
        updatePageOptions();
    </script>";

    return $script;
}

function cfield($label, $id, $name, $value, $isChecked = false)
{
    $checkedAttribute = $isChecked ? 'checked' : '';

    $html = '<div class="form-group">';
    $html .= '<label for="' . $id . '">' . $label . '</label>';
    $html .= '<input type="checkbox" id="' . $id . '" name="' . $name . '" value="' . $value . '" ' . $checkedAttribute . '>';
    $html .= '</div>';

    return $html;
}
function sbox($label, $id, $isChecked = false)
{
    // Use the ternary operator to set the 'checked' attribute
    $checkedAttribute = $isChecked ? 'checked' : '';

    $html = '<div class="form-check form-switch">';
    $html .= '<input class="form-check-input" type="checkbox" name="' . $id . '" role="switch" id="' . $id . '" ' . $checkedAttribute . '>';
    $html .= '<label class="form-check-label" for="' . $id . '">' . $label . '</label>';
    $html .= '</div>';
    $html .= '<script>
        // Get the switch checkbox element
        var switchCheckbox = document.getElementById("' . $id . '");
        
        // Add an event listener to handle the click event
        switchCheckbox.addEventListener("click", function() {
            // Update the value based on the checkbox state
            var newValue = switchCheckbox.checked ? 1 : 0;
            
            // Log the new value (you can replace this with your desired logic)
            console.log(newValue);
        });
    </script>';

    return $html;
}

$formend = ' </form>

</div> <!-- end card-body -->
</div> <!-- end card-->
</div> <!-- end col -->
</div>';
?>
<script>
    var switchCheckbox = document.getElementById('flexSwitchCheckChecked');
    switchCheckbox.addEventListener('click', function() {
        switchCheckbox.checked = !switchCheckbox.checked;
        console.log('Switch checkbox value changed to:', switchCheckbox.checked ? 1 : 0);
    });
</script>