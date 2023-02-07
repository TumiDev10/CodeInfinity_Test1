<?php
    require 'vendor\autoload.php';

    //Inititaliation.

    $name = $surname = $ID = $DOB = $errors = '';
    $NameErr = $SurnameErr = $IDErr = $DOBErr = '';
    $has_Error = false;
    $ID_in_databse = [];
    
    //This is the connection to the mongodb.

    $Client = new MongoDB\Client("mongodb://localhost:27017");
    $Collection = $Client -> Client_Info -> Client_info;

    // Loads all current Client ID's into an array - Skips if there is no Clients in collection.
    $cursor = $Collection -> find();
    
    if(!empty($cursor)) 
    {

        foreach ( $cursor as $id => $value )
        {
            array_push($ID_in_databse, $value['ID']);
        } 
    }

    //Clears variables if Cancel button is pressed 
    if(isset($_POST['cancel'])) 
    {
        $name = $surname = $ID = $DOB = '';
        $NameErr = $SurnameErr = $IDErr = $DOBErr = '';
        $has_Error = false;    
    }

    //Validates and sanities inputs - Provides aprropriate error messages.
    if(isset($_POST['submit'])) 
    {
        
        //Name
        if (empty($_POST['fName'])) 
        {
            $NameErr = 'Name is required <br>';
            $name = '';    
        }

        else
        {
            $name = filter_input(INPUT_POST, 'fName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }

        //Surname
        if (empty($_POST['sName'])) 
        {
            $SurnameErr = 'Surname is required <br>'; 
            $surname = '';   
        }
        
        else 
        {
            $surname = filter_input(INPUT_POST, 'sName', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        }
        
        //ID
        if (!(strlen($_POST['IDNum']) == 13)) 
        {
            $IDErr = 'ID Number must be 13 digits long <br>';  
            $ID = '';  
        } 
        
        else 
        {
            if(!(ctype_digit($_POST['IDNum']) == true))
            {
                $IDErr = 'ID Number may only contain digits <br>';
                $ID = '';
            } 
            
            else 
            {
                if( in_array($_POST['IDNum'], $ID_in_databse)) 
                {
                    $IDErr = 'ID number has already been used <br>';
                } 
                
                else 
                {
                    $ID = filter_input(INPUT_POST, 'IDNum', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                } 
            }
        }

        //DOB
        if(empty($ID)) 
        {
            $DOBErr = 'Correct ID number is required to verify date of birth <br>';
            $DOB = '';
        } 
        
        else 
        {
            $Validate = substr($ID, 0,2) . '/' . substr($ID, 2, 2) . '/' . substr($ID, 4, 2);

            if(!$_POST['DOB'] == $Validate) 
            {
                $DOBErr = 'Date of Birth on ID, does not match provided Date of Birth <br>';
                $DOB = '';
            } 
            
            else 
            {
                $DOB = date('d/m/Y', strtotime((filter_input(INPUT_POST, 'DOB', FILTER_SANITIZE_SPECIAL_CHARS))));
            }
        }
    }

    //Checks if there are errors - adds to database if none.
    if (empty($NameErr) && empty($SurnameErr) && empty($IDErr) && empty($DOBErr) )
    {
        // add to data base
        if(!empty($ID)) 
        {
            $Collection -> insertOne( ['fName' => $name, 'sName' => $surname, 'ID' => $ID, 'DOB' => $DOB ]);      
        }
        
        //Clears inputs of already added data.
        $name = $surname = $ID = $DOB = '';
        $NameErr = $SurnameErr = $IDErr = $DOBErr = '';
        $has_Error = false; 


    } 
    
    else
    {

        $errors = "$NameErr  $SurnameErr $IDErr $DOBErr";
        $has_Error = true;

    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Style.css">
    <title>Code Infintity - Test 1</title>

</head>
<body>
    <div id="Header" class="Header">
        <img src="CodeInfinityLogo.jpg">
        

        <!-- Displays errors if any -->
    <?php
        if($has_Error) 
        { 
          ?>
            
            <div id="error_box" class="error_box" style=" background-color: #FFCCCB; padding: 10px"}>
            <?php echo $errors?>
            </div>

        <?php 
        }
    ?>

    </div>


    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
        <label for="fName">Name</label>
        <input type="text" id="Name" name="fName" placeholder="Tumi" value="<?php echo $name; ?>">
        
        <label for="sName">Surname</label>
        <input type="text" id="Surname" name="sName" placeholder="Mashigo" value="<?php echo $surname; ?>">
        
        <label for="IDNum">ID Number</label>
        <input type="text" id="ID" name="IDNum" placeholder="9808235066088" value="<?php echo $ID; ?>">
        
        <label for="DOB">Date of Birth </label>
        <input type="date" id="DOB" name="DOB" placeholder="<?php date('d/m/Y')?>" value="<?php echo $DOB; ?>">
        
        <div id="Button">
            <input type="Submit" value="Post" id="Submit" name="submit">
            <input type="Submit" value="Cancel" id="Cancel" name="cancel">
        </div>
    
    </form>
</body>
</html>

