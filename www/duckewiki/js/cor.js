function HSVtoRGB(h, s, v) { // from http://stackoverflow.com/a/17243070/1086511 (h, s, v entre 0 e 1)
	var r, g, b, i, f, p, q, t;
    i = Math.floor(h * 6);
    f = h * 6 - i;
    p = v * (1 - s);
    q = v * (1 - f * s);
    t = v * (1 - (1 - f) * s);
    switch (i % 6) {
        case 0: r = v, g = t, b = p; break;
        case 1: r = q, g = v, b = p; break;
        case 2: r = p, g = v, b = t; break;
        case 3: r = p, g = q, b = v; break;
        case 4: r = t, g = p, b = v; break;
        case 5: r = v, g = p, b = q; break;
    }
    return {
        r: Math.round(r*255),g: Math.round(g*255),b: Math.round(b*255)
    };
}
function RGBtoHSV(r, g, b) {
	cMax = Math.max(r,g,b);
	cMin = Math.min(r,g,b);
	d = cMax-cMin;
	var H, S, V;
	if (cMax == r) {
		if (g == b && d == 0) {
			H = 0;
		} else {
			H = 60*(((g-b)/d) % 6);
		}
	} else
	if (cMax == g) {
		H = 60*(((b-r)/d) + 2);
	} else
	if (cMax == b) {
		H = 60*(((r-g)/d) + 4);
	}
	if (cMax == 0) {
		S = 0;
	} else {
		S = d/cMax;
	}
	V = cMax;
	return { h:H/360, s:S, v:V }
}
var w=350,h=250; // width/height da caixa de diálogo (divCor) (modificar também no HTML)
var x0=410,y0=180;
var corX=1, corY=1, hue=0;
var mouseIsWhere = '';
var mapa, huemap, cnv, ctx, field;
function setPixel(imgData, pxx, pxy, r, g, b, a) {
	var i = (pxx + pxy * imgData.width) * 4;
	imgData.data[i+0] = r;
	imgData.data[i+1] = g;
	imgData.data[i+2] = b;
	imgData.data[i+3] = a;
}
function showRGB(x,y) {
	var cor = HSVtoRGB(hue,corX,corY);
	ctx.fillStyle = "#E0E0E0";
	ctx.fillRect(h+23,45,w-h-26,120);
	ctx.fillStyle = "#000000";
	ctx.textAlign = "left";
	/*ctx.fillText("R: "+cor.r,h+30,60);
	ctx.fillText("G: "+cor.g,h+30,75);
	ctx.fillText("B: "+cor.b,h+30,90);*/
	ctx.fillText("R: "+int2hex(cor.r)+'|'+cor.r,h+30,60);
	ctx.fillText("G: "+int2hex(cor.g)+'|'+cor.g,h+30,75);
	ctx.fillText("B: "+int2hex(cor.b)+'|'+cor.b,h+30,90);
	ctx.fillText("hue: "+Math.round(hue*1000)/1000,h+30,120);
	ctx.fillText("sat: "+Math.round(corX*1000)/1000,h+30,135);
	ctx.fillText("val: "+Math.round(corY*1000)/1000,h+30,150);
}
function cross(x,y) {
	if (x < 3.5) { x = 3.5; } else
	if (x > h-3.5) { x = h-3.5; }
	if (y < 3.5) { y = 3.5; } else
	if (y > h-3.5) { y = h-3.5; }
	var x0 = x-5;
	if (x0 < 3.5) { x0 = 3.5; }
	var x1 = x+5;
	if (x1 > h-3.5) { x1 = h-3.5; }
	var y0 = y-5;
	if (y0 < 3.5) { y0 = 3.5; }
	var y1 = y+5;
	if (y1 > h-3.5) { y1 = h-3.5; }
	ctx.beginPath();
	ctx.moveTo(x0,y);
	ctx.lineTo(x1,y);
	ctx.moveTo(x,y0);
	ctx.lineTo(x,y1);
	ctx.stroke();
}
function showHue(x,y) {
	var cor;
	if (y <= 2.5) {
		hue = 0;
	} else
	if (y > h-2.5) {
		hue = 1;
	} else {
		hue = (y-3)/(h-6);
	}

	ctx.putImageData(huemap,h,3); // desenha a escala de cores (hue)
	ctx.strokeStyle = '#000000';
	ctx.beginPath();
	ctx.moveTo(h,3.5+hue*(h-7));
	ctx.lineTo(h+20,3.5+hue*(h-7));
	ctx.stroke();

	for (i=0; i<h-6; i++) {
		for (j=0; j<h-6; j++) {
			cor = HSVtoRGB(hue,i/(h-6),1-j/(h-6));
			setPixel(mapa,i,j,cor.r,cor.g,cor.b,255);
		}
	}
	ctx.putImageData(mapa,3,3);
	var xc = 2.5+corX*(h-5);
	var yc = 2.5+(1-corY)*(h-5);
	cor = HSVtoRGB(hue,corX,corY);
	ctx.strokeStyle = 'rgba('+(255-cor.r)+','+(255-cor.g)+','+(255-cor.b)+',255)';
	cross(xc,yc);
	// desenha a barra 'cor atual'
	ctx.fillStyle = 'rgba('+cor.r+','+cor.g+','+cor.b+',255)';
	ctx.fillRect(h+23.5,23,w-h-27,20);
	showRGB(x,y); // mostra os números RGB e HSV
}
function showCor(x,y) {
	if (x <= 2.5) { x = 2.5; }
	if (x > h-2.5) { x = h-2.5; }
	corX = (x-2.5)/(h-5);

	if (y <= 2.5) { y = 2.5; }
	if (y > h-2.5) { y = h-2.5; }
	corY = 1 - (y-2.5)/(h-5);

	ctx.putImageData(mapa,3,3);
	var cor = HSVtoRGB(hue,corX,corY);
	ctx.strokeStyle = 'rgba('+(255-cor.r)+','+(255-cor.g)+','+(255-cor.b)+',255)';
	cross(x,y);
	// desenha a barra 'cor atual'
	ctx.fillStyle = 'rgba('+cor.r+','+cor.g+','+cor.b+',255)';
	ctx.fillRect(h+24,23,w-h-27,19);
	showRGB(x,y);
}
function int2hex(n) {
	var r = n.toString(16).toUpperCase();
	if (r.length == 1) {
		r = '0'+r;
	}
	return r;
}
/** desenha o retângulo (50x30) com a cor selecionada no cnv especificado */
function carregaCor(cnv,cor) {
	var c = document.getElementById(cnv);
	var ct = c.getContext("2d");
	ct.strokeStyle = '#000000';
	ct.fillStyle = cor;
	if (!isNaN(cnv.substr(3))) { // número -> drawForm
		ct.strokeRect(0.5,0.5,49,19); // usar getW e getH
		ct.fillRect(1,1,48,18); // usar getW e getH
	} else { // texto -> config
		ct.strokeRect(0.5,0.5,49,29); // usar getW e getH
		ct.fillRect(1,1,48,28); // usar getW e getH
	}
}
function clicaCancel() {
	var div = document.getElementById('divCor');
	div.style.display = 'none';
}
/** atualiza o vetor das cores selecionadas (Cores), atualiza a tela (label,cnv,txt)
*/
function clicaOk(fonte) {
	var cor = HSVtoRGB(hue,corX,corY), label, txt;
	cor = int2hex(cor.r) + int2hex(cor.g) + int2hex(cor.b);
	if (fonte == 1) { // config
		Cores[corF.indexOf(which)] = cor;
		label = document.getElementById('lbl'+which);
		carregaCor('cnv'+which,'#'+cor); // colore o retângulo
		txt = document.getElementsByName('txt'+which)[0];
		txt.value = cor; // atualiza o input hidden para o submit
		var sel = document.getElementById('selcores');
		sel.selectedIndex = 0;
		schema = null;
	} else { // fonte = 2 -> drawForm
		label = document.getElementById('lbl'+field);
		carregaCor('cnv'+field,'#'+cor); // colore o retângulo
		txt = document.getElementsByName('txt'+field)[0];
		var cnvF = document.getElementById('cnv'+field);
		cnvF.onclick = function() { getCor(field,cor); };
	}
	label.innerHTML = '#'+cor;
	clicaCancel();
}
function cnvMouseDown(e,fonte) {
	var x = e.pageX-x0-1;
	var y = e.pageY-y0-1;
	if (x >= h && x < h+20 && y >= 2.5 && y < h-2.5) {
		mouseIsWhere = 'hue';
		showHue(x,y);
	} else
	if (x >= 2.5 && x < h-2.5 && y >= 2.5 && y < h-2.5) {
		mouseIsWhere = 'box';
		showCor(x,y);
	} else
	if (x >= h+23.5 && x < w-3.5 && y >= h-66 && y < h-36) {
		clicaOk(fonte);
	} else
	if (x >= h+23.5 && x < w-3.5 && y >= h-33.5 && y < h-3.5) {
		clicaCancel();
	}
}
function cnvMouseMove(e) {
	if (mouseIsWhere != '') {
		var x = e.pageX-x0-1;
		var y = e.pageY-y0-1;
		if (mouseIsWhere == 'hue') {
			showHue(x,y);
		} else
		if (mouseIsWhere == 'box') {
			showCor(x,y);
		}
	}
}
function mouseMove(e) {
	if (mouseIsWhere != '') {
		var x = e.pageX-x0-1;
		var y = e.pageY-y0-1;
		if (mouseIsWhere == 'box') {
			showCor(x,y);
		}
	}
}
function keyUp(e) {
	if (e.keyCode == 27) { // Esc
		clicaCancel();
	}
}
function mouseUp() {
	mouseIsWhere = '';
}
function getCor(who,rgb) {
	var origcor, div;
	if (!isNaN(who)) { // se é número, vem do field de um formulário
		origcor = '#'+rgb;
		cnv = document.getElementById('cnvGetCor');
		cnv.width = w;
		cnv.height = h;
		ctx = cnv.getContext('2d');
		field = who;
		//div = document.getElementById('divcor'+who);
	} else { // se é texto, vem da tela de config
		origcor = '#'+Cores[corF.indexOf(who)];
		which = who;
	}
	div = document.getElementById('divCor');
	div.style.display = 'block';
	div.style.left = x0+'px';
	div.style.top = y0+'px';
	ctx.strokeStyle = '#000000';
	ctx.strokeRect(0,0,w,h); // borda preta
	ctx.fillStyle = '#E0E0E0'; // fundo da caixa de diálogo
	ctx.fillRect(1,1,w-2,h-2);
	ctx.strokeStyle = '#808080';
	ctx.strokeRect(2.5,2.5,h-5,h-5); // retângulo de seleção
	ctx.strokeRect(h,2.5,20,h-5); // barra de hue
	ctx.strokeRect(h+23.5,2.5,w-h-26,40); // cor anterior/nova
	ctx.fillStyle = origcor;
	ctx.fillRect(h+24,3,w-h-27,39);

	huemap = ctx.createImageData(20,h-6);
	var i,cor;
	for (i=0; i<h-6; i++) {
		cor = HSVtoRGB(i/(h-6),1,1);
		for (j=0; j<20; j++) {
			setPixel(huemap,j,i,cor.r,cor.g,cor.b,255);
		}
	}
	ctx.putImageData(huemap,h,3); // desenha a escala de cores (hue)
	var origR = hex2int(origcor.substr(1,2))/255;
	var origG = hex2int(origcor.substr(3,2))/255;
	var origB = hex2int(origcor.substr(5,2))/255;
	var hsv = RGBtoHSV(origR,origG,origB);
	hue = hsv.h;
	ctx.strokeStyle = '#000000';
	ctx.beginPath();
	ctx.moveTo(h,3.5+hue*(h-7));
	ctx.lineTo(h+20,3.5+hue*(h-7));
	ctx.stroke();

	// botões
	ctx.textAlign = "center";
	ctx.fillStyle = '#000000';
	ctx.strokeRect(h+23.5,h-66.5,w-h-27,30); // botão OK
	ctx.fillText('OK',h+59,h-47);
	ctx.strokeRect(h+23.5,h-33.5,w-h-27,30); // botão Cancela
	ctx.fillText('Cancela',h+59,h-15);

	mapa = ctx.createImageData(h-6,h-6); // onde vai desenhar a escala de value/saturation
	for (i=0; i<h-6; i++) {
		for (j=0; j<h-6; j++) {
			cor = HSVtoRGB(hue,i/(h-6),1-j/(h-6));
			setPixel(mapa,i,j,cor.r,cor.g,cor.b,255);
		}
	}
	ctx.putImageData(mapa,3,3);
	
	corX = hsv.s;
	corY = hsv.v;
	var xc = 2.5+corX*(h-5);
	var yc = 2.5+(1-corY)*(h-5);
	cor = HSVtoRGB(hue,corX,corY);
	ctx.strokeStyle = 'rgba('+(255-cor.r)+','+(255-cor.g)+','+(255-cor.b)+',255)';
	cross(xc,yc);
	showRGB(corX,corY);
}
function lblClick(who) {
	var resp = prompt('#RRGGBB:\n(Red, Green e Blue\nno formato hexadecimal,\nde 00 a FF cada)',who.innerHTML);
	if (resp != '' && resp != null) {
		who.innerHTML = resp;
		var id = who.id.substr(3);
		Cores[corF.indexOf(id)] = resp.substr(1);
		carregaCor('cnv'+id,resp);
	}
}
function btncfgCancelClick() {
	// desfaz a visualização
	this.close();
}
window.addEventListener('mousemove', mouseMove, false);
window.addEventListener('mouseup', mouseUp, false);
window.addEventListener('keyup', keyUp, false);
