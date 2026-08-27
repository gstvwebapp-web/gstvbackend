@extends('layouts.sudokuapp')

@section('meta-title')
GSTV | Sudoku
@endsection
 
@section('content')

<link rel="stylesheet" media="all" type="text/css" href="{{config('app.url')}}/public/assets/css/sudokuJS.css">
<style>
    * {
        margin:0; padding:0;
        -moz-box-sizing: border-box;
        -webkit-box-sizing: border-box;
        box-sizing: border-box;
    }
    .wrap {
        padding: 2em 1.3em;
        width: 400px;
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
    }

    @media(min-width: 30em){
        .wrap{
            width: 490px;
        }
        .sudoku-board input {
            font-size: 24px;
            font-size: 1.5rem;
        }
        .sudoku-board .candidates {
            font-size: .8em;
        }
    }
    a,#iz-newshub-container,#openModal1,#overlay{display: none !important;}

</style>
 <script type="text/javascript" src="{{config('app.url')}}/public/assets/js/sudokuJS.js"></script>

	<div class="wrap">
		<button type="button" class="sudokubutton2 js-generate-board-btn--medium">New Generate</button> 
		<!--the only required html-->
		<div id="sudoku" class="sudoku-board">
		</div>

		<div class="bottomBtnGroup">
			<!--solve buttons-->
			<div class="solveBtnbox">
				<!-- <span>Solve</span> -->
				<button type="button" class="sudokubutton5 js-solve-step-btn">Solve One Step</button>
				<button type="button" class="sudokubutton6 js-solve-all-btn">Solve All</button>
			</div>
			<!--clear board btn-->
			<div class="clearBtnbox">
				<!-- <span>Clear</span> -->
				<button type="button" class="sudokubutton7 js-clear-board-btn">Clear Board</button>
			</div>
		</div>
		

	</div>

	<script>
		$(document).on("keydown", ".sudoku-board input", function (event) {
    // Allow: backspace (8), delete (46), tab (9), enter (13), arrow keys (37-40)
    if ($.inArray(event.keyCode, [8, 9, 13, 37, 38, 39, 40, 46]) !== -1) {
        return;
    }
    // Allow only numbers (0-9)
    if (event.keyCode < 48 || event.keyCode > 57) {
        event.preventDefault();
    }
});
		var	$candidateToggle = $(".js-candidate-toggle"),
			 
			$generateBoardBtnMedium = $(".js-generate-board-btn--medium"),

			$solveStepBtn = $(".js-solve-step-btn"),
			$solveAllBtn = $(".js-solve-all-btn"),
			$clearBoardBtn = $(".js-clear-board-btn"),

			mySudokuJS = $("#sudoku").sudokuJS({
				difficulty: "very hard"
				//change state of our candidate showing checkbox on change in sudokuJS
				,candidateShowToggleFn : function(showing){
					$candidateToggle.prop("checked", showing);
				}
			});

		$solveStepBtn.on("click", mySudokuJS.solveStep);
		$solveAllBtn.on("click", mySudokuJS.solveAll);
		$clearBoardBtn.on("click", mySudokuJS.clearBoard);

		 
		$generateBoardBtnMedium.on("click", function(){
			mySudokuJS.generateBoard("medium");
		});
		 
		$candidateToggle.on("change", function(){
			if($candidateToggle.is(":checked"))
				mySudokuJS.showCandidates();
			else
				mySudokuJS.hideCandidates();
		});
		$candidateToggle.trigger("change");
	</script>

@endsection
