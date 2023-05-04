{"version":3,"sources":["text.js"],"names":["BX","namespace","escapeText","Landing","Utils","headerTagMatcher","Matchers","headerTag","changeTagName","textToPlaceholders","isTable","currentTable","isSelectedAll","Block","Node","Text","options","Runtime","loadExtension","apply","this","arguments","type","onClick","bind","onPaste","onDrop","onInput","onMousedown","onMouseup","node","addEventListener","document","currentNode","prototype","__proto__","superClass","constructor","onAllowInlineEdit","setAttribute","Loc","getMessage","onChange","preventAdjustPosition","preventHistory","call","UI","Panel","EditorPanel","getInstance","adjustPosition","History","push","Entry","block","getBlock","id","selector","command","undo","lastValue","redo","getValue","tBody","getElementsByTagName","tableContainerWidth","getBoundingClientRect","width","tBodyWidth","classList","add","remove","event","clearTimeout","inputTimeout","key","keyCode","which","top","window","navigator","userAgent","match","ctrlKey","metaKey","setTimeout","tableFontSize","parseInt","getComputedStyle","srcElement","getPropertyValue","textContent","contains","onEscapePress","isEditable","hide","disableEdit","preventDefault","clipboardData","getData","sourceText","encodedText","encode","formattedHtml","replace","RegExp","execCommand","text","onDocumentClick","fromNode","manifest","group","allowInlineEdit","Main","isControlsEnabled","stopPropagation","enableEdit","nodeTableContainerList","forEach","tableContainer","tableEditor","unselect","Tool","ColorPicker","hideAll","Button","FontAction","requestAnimationFrame","target","nodeName","parentElement","range","createRange","selectNode","getSelection","removeAllRanges","addRange","addTableButtons","isContentEditable","querySelectorAll","length","nodeTableContainer","TableEditor","default","StylePanel","isShown","buttons","getDesignButton","isHeader","getChangeTagButton","onChangeHandler","onChangeTag","textOnly","show","contentEditable","table","hasAttribute","prepareNewTable","designButton","Design","html","attrs","title","onDesignShow","code","isAllowInlineEdit","getField","field","Field","name","content","innerHTML","changeTagButton","setValue","value","preventSave","isSavePrevented","querySelector","cloneNode","prepareTable","test","nodeIsTable","tdTag","neededButtons","setTd","tableButtons","getTableButtons","tableAlignButtons","isCell","isButtonAddRow","isButtonAddCol","isNeedTablePanel","hideButtons","nodeTableList","nodeTable","tableButton","parentNode","children","Array","from","getAmountTableRows","neededButon","childNodes","childNodesArray","childNodesArrayPrepare","childNode","nodeType","neededPosition","indexOf","rows","row","rowChildPrepare","rowChildNode","getAmountTableCols","th","insertAfter","activeAlignButtonId","setActiveAlignButtonId","undefined","count","isIdentical","tableAlignButton","layout","ChangeTag","toLowerCase","activateItem","AlignTable","ColorAction","DeleteElementTable","StyleTable","CopyTable","DeleteTable","data","changeOptionsHandler","setClassesForRemove","className","element"],"mappings":"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","file":"text.map.js"}