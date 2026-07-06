import { useMemo, useState } from "react";

/* ==========================================================
   RegisterAgreement.tsx

   UNOTRAVEL Register Agreement Page

   사용 페이지
   - /register/agreement

   백엔드 연동
   ------------------------------------------
   agreement check       ← 기존 regis_agree.php 약관 동의 대응
   register form         ← 회원가입 입력 페이지 이동

   Header / Footer는 App.tsx 공통 컴포넌트 사용
========================================================== */

const TERMS_HTML = `<div class="agree">
	<dl>
		<dt>제 1 장 총칙</dt>

	 

	<dt>제 1 조(목적)</dt>

	 

	<dd>이 약관은 우노트래블이 운영하는 (이하 "당사"이라 한다)에서 제공하는 인터넷 관련 서비스(이하 "서비스"라 한다)를 이용함에 있어 이용자의 권리•의무 및 책임사항을 규정함을 목적으로 합니다.

	※「PC 통신,모바일 무선 등을 이용하는 전자거래에 대해서도 그 성질에 반하지 않는 한 이 약관을 준용합니다.」
	</dd>
	 

	 

	<dt>제 2 조(정의)</dt>

	 

	<dd>① "당사"란 우노트래블이 운영하는 재화 또는 용역(이하 "재화 등"이라 함)을 이용자에게 제공하기 위하여 컴퓨터 등 정보통신설비를 이용하여 재화 등을 거래할 수 있도록 설정한 가상의 영업장을 말하며, 아울러 사이버 몰을 운영하는 사업자의 의미로도 사용합니다.<br>

	② "이용자"란 "당사"홈페이지에 접속하여 이 약관에 따라 "당사"가 제공하는 서비스를 받는 회원 및 비회원을 말합니다.<br>

	③ "회원"이라 함은 "당사"에 개인정보를 제공하여 회원등록을 한 자로서, "당사"의 정보를 지속적으로 제공받으며, "당사"가 제공하는 서비스를 계속적으로 이용할 수 있는 자를 말합니다.<br>

	④ "비회원"이라 함은 회원에 가입하지 않고 "당사"가 제공하는 서비스를 이용하는 자를 말합니다. 
	</dd>
	 

	 

	<dt>제 3 조(약관의 명시와 개정)</dt>

	 

	<dd>① "당사"는 이 약관의 내용과 상호 및 대표자 성명, 영업소 소재지, 주소(소비자의 불만을 처리할 수 있는 곳의 주소를 포함),전화번호, 모사전송번호, 전자우편주소, 사업자등록번호,

	통신판매업신고번호, 개인정보관리책임자 등을 이용자가 쉽게 알 수 있도록 “당사” 홈페이지의 초기 서비스화면(전면)에 게시합니다. 다만 약관의 내용은 이용자가 연결화면을 통하여 볼 수 있도록 할 수 있습니다.<br>

	② "당사"는 이용자가 약관에 동의하기에 앞서 약관에 정하여져 있는 내용 중 투어계약•환불조건 등과 같은 중요한 내용을 이용자가 이해할 수 있도록 별도의 연결화면 또는 팝업화면 등을 제공하여 이용자의 확인을 구하여야 합니다.<br>

	③ "당사"는 전자상거래 등에서의 소비자 보호에 관한 법률, 약관의 규제에 관한 법률, 전자거래기본법, 전자서명법, 정보통신망이용촉진 등에 관한법률,방문판매등에관한법률,소비자보호법 등 관련법을 위배하지 않는 범위에서 이 약관을 개정할 수 있습니다<br>

	④ "당사"가 약관을 개정할 경우에는 적용일자 및 개정사유를 명시하여 현행 약관과 함께 “당사”홈페이지의 초기화면에 그 적용일자 7 일 이전부터 적용일자 전일까지 공지합니다. 다만, 이용자에게 불리하게 약관내용을 변경하는 경우에는 최소한 30 일 이전의 유예기간을 두고 공지합니다. 이 경우 "당사"는 개정 전 내용과 개정 후 내용을 명확하게 비교하여 이용자가 알기 쉽도록 표시합니다.<br>

	⑤ "당사"가 약관을 개정할 경우에는 그 개정약관은 적용일자 이후에 체결되는 계약에만 적용되고 그 이전에 이미 체결된 계약에 대해서는 개정 전의 약관조항이 그대로 적용됩니다. 다만 이미 계약을 체결한 이용자가 개정약관 조항의 적용을 받기를 원하는 뜻을 제 3 항에 의한 개정 약관의 공지기간 내에 "당사"에 송신하여 동의를 받은 경우에는 개정약관 조항이 적용됩니다.<br>

	⑥ 이 약관에서 정하지 아니한 사항과 이 약관의 해석에 관하여는 전자상거래등에서의소비자보호에관한법률,약관의규제등에관한법률,정부가 제정한 전자상거래

	등에서의 소비자보호지침 및 관계법령 또는 상 관례에 따릅니다. 
	</dd>
	 

	 

	<dt>제 4 조(서비스의 제공 및 변경)</dt>

	 

	<dd>① "당사"는 다음과 같은 업무를 수행합니다.<br>

	<p class="mgl30">1.재화 또는 용역 등에 대한 정보 제공 및 계약의 체결<br>
	2.계약이 체결된 재화 또는 용역 등의 배송<br>
	3.기타 "당사"가 정하는 업무</p>

	② "당사"는 재화 또는 용역의 품절 또는 기술적 사양의 변경 등의 경우에는 장차 체결되는 계약에 의해 제공할 재화 또는 용역의 내용을 변경할 수 있습니다.이 경우에는 변경된 재화 또는 용역의 내용 및 제공일자를 명시하여 현재의 재화 또는 용역의 내용을 게시한 곳에 즉시 공지합니다.<br>

	⑤ "당사"가 제공하기로 이용자와 계약을 체결한 서비스의 내용을 재화 등의 품절 또는 기술적 사양의 변경 등의 사유로 변경할 경우에는 그 사유를 이용자에게 통지 가능한 주소로 즉시 통지합니다.<br>

	⑥ 전항의 경우 "당사"는 이로 인하여 이용자가 입은 인과관계가 입증된 실제 손해를 배상합니다. 다만,"당사"가 고의 또는 과실이 없음을 입증하는 경우에는 그러하지 아니합니다.
	</dd>
	 

	 

	<dt>제 5 조(서비스의 중단)</dt>

	 

	<dd>① "당사"는 컴퓨터 등 정보통신설비의 보수 점검•교체 및 고장, 통신의 두절 등의 사유가 발생한 경우에는 서비스의 제공을 일시적으로 중단할 수 있습니다.<br>

	② "당사"는 제 1 항의 사유로 서비스의 제공이 일시적으로 중단됨으로 인하여 이용자 또는 제 3 자가 입은 손해에 대하여 배상합니다. 단 "당사"에 고의 또는 과실이 없는 경우에는 그러하지 아니합니다.<br>

	③ 사업종목의 전환, 사업의 포기, 업체간 통합 등의 이유로 서비스를 제공할 수 없게 되는 경우에는 "당사”는 제 8 조에 정한 방법으로 이용자에게 통지하고 당초 "당사"에서 제시한 조건에 따라 소비자에게 보상합니다.
	</dd>
	 

	 

	<dt>제 6 조(회원가입)</dt>

	 

	<dd>① 이용자는 "당사"가 정한 가입 양식에 따라 회원정보를 기입한 후 이 약관에 동의한다는 의사표시를 함으로서 회원가입을 신청합니다.<br>

	② "당사"는 제 1 항과 같이 회원으로 가입할 것을 신청한 이용자 중 다음 각 호에 해당하지 않는 한 회원으로 등록합니다.<br>

	<p class="mgl30">1.가입신청자가 이 약관 제 7 조제 3 항에 의하여 이전에 회원자격을 상실한 적이 있는 경우, 다만 제 7 조제 3 항에 의한 회원자격 상실 후 3 년이 경과한 자로서 "당사"의 회원 재가입 승낙을 얻은 경우에는 예외로 한다.<br>

	2.등록 내용에 허위, 기재누락, 오기가 있는 경우<br>

	3.기타 회원으로 등록하는 것이 "당사"의 기술상 현저히 지장이 있다고 판단되는 경우</p>

	③ 회원가입의 성립 시기는 "당사"의 승낙이 회원에게 도달한 시점으로 합니다.<br>

	④ 회원은 제 16 조 제 1 항에 의한 등록사항에 변경이 있는 경우, 즉시 전자우편 및 기타 방법으로 “당사”에 그 변경사항을 알려야 합니다.<br>
	</dd>
	 

	 

	<dt>제 7 조(회원 탈퇴 및 자격 상실 등)</dt>

	 

	<dd>① 회원은 "당사"에 언제든지 탈퇴를 요청할 수 있으며 "당사"는 즉시 회원 탈퇴를 처리합니다.<br>

	② 회원이 다음 각 호의 사유에 해당하는 경우,"당사"는 회원자격을 제한 및 정지시킬 수 있습니다.<br>

	<p class="mgl30">1.가입 신청 시에 허위 내용을 등록한 경우<br>

	2."당사"를 이용하여 구입한 재화 등의 대금, 기타 "당사"이용에 관련하여 회원이 부담하는 채무를 기일에 지급하지 않는 경우<br>

	3.다른 사람의 "당사"이용을 방해하거나 그 정보를 도용하는 등 전자상거래질서를 위협하는 경우<br>

	4."당사"를 이용하여 법령 또는 이 약관이 금지하거나 공서양속에 반하는 행위를 하는 경우<br>

	5.기타 다음과 같은 행위 등으로 "당사"의 건전한 운영을 해하거나 "당사"의 업무를 방해하는 경우</p>

	<p class="mgl40">가."당사"의 운영에 관련하여 근거 없는 사실 또는 허위의 사실을 적시하거나 유포하여 "당사"의 명예를 실추시키고 "당사"의 신뢰성을 해하는 경우<br>

	나."당사"의 운영과정에서 직원에게 폭언 또는 음란한 언행을 하여 업무환경을 심각히 해하는 경우다."당사"의 운영과정에서 이유 없는 잦은 연락이나 소란 또는 협박, 인과관계가 입증되지 않는 피해에 대한 보상(적립금, 현금, 상품)요구 등으로 업무를 방해하는 경우<br>

	라."당사"를 통해 구입한 상품 또는 용역에 특별한 하자가 없는데도 불구하고 일부 사용 후 상습적인 취소•전부 또는 일부 반품 등으로 회사의 업무를 방해하는 경우. 단, 당해 회원의 취소 반품비율이 회사의 평균 취소 반품율보다 50%이상 높을 경우에는 상습적인 것으로 인정될 수 있습니다</p>

	③ "당사"가 회원 자격을 제한•정지 시킨 후 동일한 행위가 2 회 이상 반복되거나 30 일 이내에 그 사유가 시정되지 아니하는 경우 "당사"는 회원자격을 상실시킬 수 있습니다.<br>

	⑤ "당사"가 회원자격을 상실시키는 경우에는 회원등록을 말소합니다. 이 경우 회원에게 이를 통지하고 회원등록 말소 전에 최소한 30 일 이상의 기간을 정하여 소명할 기회를 부여합니다.<br>
	</dd>
	 

	 

	<dt>제 8 조(회원에 대한 통지)</dt>

	 

	<dd>① "당사"가 회원에 대한 통지를 하는 경우, 회원이 "당사"와 미리 약정한 전자우편 주소로 할 수 있습니다.<br>

	② "당사"는 불특정다수 회원에 대한 통지의 경우 1 주일이상 "당사"게시판에 게시함으로서 개별 통지에 갈음할 수 있습니다.다만, 회원 본인의 거래와 관련하여 중대한 영향을 미치는 사항에 대하여는 개별통지를 합니다.<br>

	 </dd>

	 

	<dt>제 9 조(구매신청)</dt>

	 

	<dd>"당사"이용자는 "당사"상에서 다음 또는 이와 유사한 방법에 의하여 구매를 신청하며,"당사"는 이용자가 구매신청을 함에 있어서 다음의 각 내용을 알기 쉽게 제공하여야 합니다. 단, 회원인 경우 제 2 호 내지 제 4 호의 적용을 제외할 수 있습니다.<br>

	<p class="mgl30">1.재화 등의 검색 및 선택<br>

	2.성명, 주소, 전화번호, 전자우편주소(또는 이동전화번호)등의 입력<br>

	3.약관내용, 청약철회권이 제한되는 서비스, 배송료, 설치비 등의 비용 부담과 관련한 내용에 대한 확인<br>

	4.이 약관에 동의하고 제 3 호의 사항을 확인하거나 거부하는 표시(예, 마우스 클릭)<br>

	5.재화 등의 구매신청 및 이에 관한 확인 또는 "당사"의 확인에 대한 동의<br>

	6.결제방법의 선택</p>
	</dd>
	 

	 

	<dt>제 10 조(계약의 성립)</dt>

	 

	<dd>① "당사"는 제 9 조와 같은 구매신청에 대하여 다음 각 호에 해당하면 승낙하지 않을 수 있습니다. 다만, 미성년자와 계약을 체결하는 경우에는 법정대리인의 동의를 얻지 못하면 미성년자 본인 또는 법정대리인이 계약을 취소할 수 있다는 내용을 고지하여야 합니다.<br>

	<p class="mgl30">1.신청내용에 허위, 기재누락, 오기가 있는 경우<br>

	2.미성년자가 담배, 주류 등 청소년보호법에서 금지하는 재화 및 용역을 구매하는 경우<br>

	3.기타 구매신청에 승낙하는 것이 "당사"기술상 현저히 지장이 있다고 판단하는 경우<br>

	4.신용카드 결제 시 소유주의 동의를 얻지 않는 불법행위로 추정 또는 확인되었을 경우<br>

	5.구매 신청 고객이 제 7 조에 따른 회원 자격 제한 •정지 고객임이 확인되었을 경우</p>

	② "당사"의 승낙이 제 12 조 제 1 항의 수신확인통지형태로 이용자에게 도달한 시점에 계약이 성립한 것으로 봅니다.<br>

	③ "당사"의 승낙의 의사표시에는 이용자의 구매 신청에 대한 확인 및 판매가능 여부,구매신청의 정정 취소 등에 관한 정보를 포함하여야 합니다.
	</dd>
	 

	 

	<dt>제 11 조(대금지급방법)</dt>

	 

	<dd>"당사"에서 구매한 재화 또는 용역에 대한 대금지급방법은 다음 각 호의 방법 중 가용한 방법으로 할 수 있습니다. 단, "당사"는 이용자의 지급방법에 대하여 재화 등의 대금에 어떠한 명목의 수수료도 추가하여 징수할 수 없습니다.<br>
	1.온라인무통장입금<br>

	2.선불카드, 직불카드, 신용카드 등의 각종 카드 결제<br>

	3.당사 내사 방문 후 대금지급
	</dd>
	 

	 

	<dt>제 12 조(수신확인통지•구매신청 변경 및 취소)</dt>

	 

	<dd>① "당사"는 이용자의 구매신청이 있는 경우 이용자에게 수신확인통지를 합니다.<br>

	② 수신확인통지를 받은 이용자는 의사표시의 불일치 등이 있는 경우에는 수신확인통지를 받은 후 즉시 구매신청 변경 및 취소를 요청할 수 있고 "당사"는 배송 전에 이용자의 요청이 있는 경우에는 지체 없이 그 요청에 따라 처리하여야 합니다. 다만, 이미 대금을 지불한 경우에는 제 15 조의 청약철회 등에 관한 규정에 따릅니다.
	</dd>
	 

	 

	<dt>제 13 조(재화 등의 공급)</dt>

	 

	<dd>① "당사"는 이용자와 재화 등의 공급시기에 관하여 별도의 약정이 없는 이상, 이용자가 청약을 한 날부터 7 일 이내에 재화 등을 배송할 수 있도록 주문제작, 포장 등 기타의 필요한 조치를 취합니다. 다만,"당사"가 이미 재화 등의 대금의 전부 또는 일부를 받은 경우에는 대금의 전부 또는 일부를 받은 날부터 2 영업일 이내에 조치를 취합니다. 이때 "당사"는 이용자가 재화 등의 공급 절차 및 진행사항을 확인할 수 있도록 적절한 조치를 합니다. 여행상품과 같은 무형의 재화 공급은 해당 상품에 적용되는 별도의 약관을 교부하고 해당 서비스가 차질 없이 진행되도록 일련의 조치를 하여야 합니다.<br>

	② "당사"는 이용자가 구매한 재화에 대해 배송수단, 수단별 배송비용 부담자, 수단별 배송기간 등을 명시합니다. 만약 "당사"가 약정 배송기간을 초과한 경우에는 그로 인한 이용자의 손해를 배상하여야 합니다. 다만,"당사"가 고의•과실이 없음을 입증한 경우에는 그러하지 아니합니다. 여행상품과 같은 무형의 재화 공급은 예약한 상품에 대한 별도의 여행자 계약서 등을 교부하여 이용자가 상기 상품의 구매와 이용에 대해 숙지할 수 있도록 하여야 합니다.

	 </dd>

	 

	<dt>제 14 조(환급)</dt>

	 

	<dd>"당사"는 이용자가 구매신청 한 재화 등이 품절 등의 사유로 인도 또는 제공을 할 수 없을 때에는 지체 없이 그 사유를 이용자에게 통지하고 사전에 재화 등의 대금을 받은 경우에는 대금을 받은 날부터 2 영업일 이내에 환급하거나 환급에 필요한 조치를 취합니다. 다만, 여행상품의 경우 상품의 특성 상 이용자가 출발일 전 모든 예약이 완료된 이후 계약을 해지할 경우 국내(외)여행표준약관 및 국내(외) 소비자 피해보상규정에 의거 손해 배상액을 공제하고 환불하며, 기타 상품의 상품이용 계약체결 시 계약한 특별약관 등의 규정에 의거한 상품의 취소 및 환불 수수료를 공제 후 환불합니다.
	</dd>
	 

	 

	<dt>제 15 조(청약철회 등)</dt>

	 

	<dd>① "당사"와 재화 등의 구매에 관한 계약을 체결한 이용자는 수신확인의 통지를 받은 날부터 7 일 이내에는 청약의 철회를 할 수 있습니다. 다만, 여행상품의 경우 국내(외)여행표준약관에 의한 환급기준에 따라 별도의 취소수수료가 부과될 수 있습니다.<br>

	② 이용자는 재화 등을 배송 받은 경우 다음 각 호의 경우에는 청약철회 및 교환을 할 수 없습니다.<br>

	<p class="mgl30">1.이용자에게 책임 있는 사유로 재화 등이 멸실 또는 훼손된 경우 (다만, 재화 등의 내용을 확인하기 위하여 포장 등을 훼손한 경우에는 사전에 청약철회 제한에 관해 고지하지 않은 한 청약철회 등을 할 수 있습니다.)<br>

	2.이용자의 사용 또는 일부 소비에 의하여 재화 등의 가치가 현저히 감소한 경우<br>

	3.시간의 경과에 의하여 재판매가 곤란할 정도로 재화 등의 가치가 현저히 감소한 경우<br>

	4.같은 성능을 지닌 재화 등으로 복제가 가능한 경우 그 원본인 재화 등의 포장을 훼손한 경우</p>

	③ 제 2 항 제 2 호 내지 제 4 호의 경우에 "당사"가 사전에 청약철회 등이 제한되는 사실을 소비자가 쉽게 알 수 있는 곳에 명기하거나 시용상품을 제공하는 등의 조치를 하지 않았다면 이용자의 청약철회 등이 제한되지 않습니다.<br>

	④ 이용자는 제 1 항 및 제 2 항의 규정에 불구하고 재화 등의 내용이 표시•광고 내용과 다르거나 계약내용과 다르게 이행된 때에는 당해 재화 등을 공급 받은 날부터 3 월 이내,그 사실을 안 날 또는 알 수 있었던 날부터 30 일 이내에 청약철회 등을 할 수 있습니다.
	</dd>
	 

	 

	<dt>제 16 조(청약철회 등의 효과)</dt>

	 

	<dd>① "당사"는 이용자로부터 재화 등을 반환 받은 경우 3 영업일 이내에 이미 지급 받은 재화 등의 대금을 환급합니다. 이 경우 "당사"이 이용자에게 재화 등의 환급을 지연한 때에는 그 지연기간에 대하여 공정거래위원회가 정하여 고시하는 지연이자율을 곱하여 산정한 지연이자를 지급합니다.<br>

	② "당사"는 위 대금을 환급함에 있어서 이용자가 신용카드 또는 전자화폐 등의 결제수단으로 재화 등의 대금을 지급한 때에는 지체 없이 당해 결제수단을 제공한 사업자로 하여금 재화 등의 대금의 청구를 정지 또는 취소하도록 요청합니다.<br>

	③ 청약철회 등의 경우 공급 받은 재화 등의 반환에 필요한 비용은 이용자가 부담합니다.<br>

	③ 이용자가 재화 등을 제공받을 때 발송비를 부담한 경우에 "당사"는 청약철회 시 그 비용을 누가 부담하는지를 이용자가 알기 쉽도록 명확하게 표시합니다.
	</dd>
	 

	 

	<dt>제 17 조(개인정보취급방침)</dt>

	 

	<dd>개인정보보호에 관한 사항은 몰에 게시된 당사의 개인정보보호정책에 규정된 내용에 따릅니다.</dd>

	 

	 

	<dt>제 18 조("당사"의 의무)</dt>

	 

	<dd>① "당사"는 법령과 이 약관이 금지하거나 공서양속에 반하는 행위를 하지 않으며 이 약관이 정하는 바에 따라 지속적이고 안정적으로 재화•용역을 제공하는 데 최선을 다하여야 합니다.<br>

	② "당사"는 이용자가 안전하게 인터넷 서비스를 이용할 수 있도록 이용자의 개인정보(신용정보 포함)보호를 위한 보안시스템을 갖추어야 합니다.<br>

	③ "당사"가 상품이나 용역에 대하여 「표시•광고의공정화에 관한 법률」제 3 조 소정의 부당한 표시•광고행위를 함으로써 이용자가 손해를 입은 때에는 이를 배상할 책임을 집니다.
	<dd>
	 

	 

	<dt>제 19 조(회원의 ID및 비밀번호에 대한 의무)</dt>

	 

	<dd>① 제 17 조의 경우를 제외한 ID 와 비밀번호에 관한 관리책임은 회원에게 있습니다.<br>

	② 회원은 자신의 ID및 비밀번호를 제 3 자에게 이용하게 해서는 안 됩니다.<br>

	④ 회원이 자신의 ID및 비밀번호를 도난당하거나 제 3 자가 사용하고 있음을 인지한 경우에는 바로 "당사"에 통보하고 "당사"의 안내가 있는 경우에는 그에 따라야 합니다.
	</dd>
	 

	 

	<dt>제 20 조(이용자의 의무)</dt>

	 

	<dd>이용자는 다음 행위를 하여서는 안 됩니다.<br>

	1.신청 또는 변경 시 허위내용의 등록<br>

	2.타인의 정보 도용<br>

	3."당사"에 게시된 정보의 변경<br>

	4."당사”가 정한 정보 이외의 정보(컴퓨터 프로그램 등)의 송신 또는 게시<br>

	5."당사"기타 제 3 자의 저작권 등 지적재산권에 대한 침해<br>

	6."당사"기타 제 3 자의 명예를 손상시키거나 업무를 방해하는 행위<br>

	7.외설 또는 폭력적인 메시지•화상•음성•기타 공서양속에 반하는 정보를 몰에 공개 또는 게시하는 행위
	</dd>
	 

	 

	<dt>제 21 조(연결 "당사"과 피연결 "당사"$간의 관계)</dt>

	 
	<dd>
	① 상위 "당사"과 하위 "당사"이 하이퍼 링크(예:하이퍼 링크의 대상에는 문자,그림 및 동화상 등이 포함됨 )방식 등으로 연결된 경우,전자를 연결 "당사"(웹사이트)이라고 하고 후자를 피연결 "당사"(웹사이트)라고 합니다.<br>

	② 연결 "당사"는 피연결 "당사"가 독자적으로 제공하는 재화 등에 의하여 이용자와 행하는 거래에 대해서 보증책임을 지지 않는다는 뜻을 피연결 "당사"의 초기화면 또는 연결되는 시점의 팝업화면으로 명시한 경우에는 그 거래에 대한 보증책임을 지지 않습니다.
	</dd>
	 

	 

	<dt>제 22 조(저작권의 귀속 및 이용제한)</dt>

	 

	<dd>① "당사"가 작성한 저작물에 대한 저작권,기타 지적재산권은 "당사"에 귀속합니다.<br>

	② 이용자는 "당사"를 이용함으로써 얻은 정보 중 "당사"에게 지적재산권이 귀속된 정보를 "당사"의 사전승낙 없이 복제,송신,출판,배포,방송,기타 방법에 의하여 영리목적으로 이용하거나 제 3 자에게 이용하게 하여서는 안 됩니다.<br>

	③ "당사"는 약정에 따라 이용자에게 귀속된 저작권을 사용하는 경우 당해 이용자에게 통보하여야 합니다.<br>

	④ 이용자는 “당사”가 제공하는 각종 서비스 등을 이용하는 과정에서 “당사”에 게시 또는 등록한 각종 저작물을 “당사”가 무상으로 사용하는 것을 허락하며,이는 이용자가 회원을 탈퇴한 경우에도 유효합니다.단,이용자가 “당사”에 대해 상기 사용권의 허락을 취소하는 통지를 한 경우에는 그러하지 아니합니다.

	 </dd>

	 

	<dt>제 23 조(회원의 게시물 및 저작권)</dt>

	 

	<dd>① 게시물이라 함은 회원이 서비스를 이용하면서 게시한 글,사진,각종 파일과 링크 등을 말합니다.<br>

	③ 회원의 게시물에 의한 손해나 기타 문제가 발생하는 경우,회원은 이에 대한 책임을 지게 되며, “당사”는 책임을 지지 않습니다.<br>

	③ “당사”는 다음 각 호에 해당하는 게시물 등을 회원의 사전 동의 없이 임의 게시, 중단, 수정, 삭제, 이동 또는 등록 거부 등의 관련 조치를 할 수 있습니다.<br>

	<p class="mgl30">- 다른 회원 또는 제 3 자에게 심한 모욕을 주거나 명예를 손상시키는 내용인 경우<br>

	- 공공질서 및 미풍양속에 위반되는 내용을 유포하거나 링크시키는 경우<br>

	- 불법복제 또는 해킹을 조장하는 내용인 경우<br>

	- 제 3 자의 저작권을 침해하여 게시중단 요청을 받은 경우<br>

	- 영리를 목적으로 하는 광고일 경우<br>

	- 범죄와 결부된다고 객관적으로 인정되는 내용일 경우<br>

	- 다른 이용자 또는 제 3 자의 저작권 등 기타 권리를 침해하는 내용인 경우<br>

	- 사적인 정치적 판단이나 종교적 견해의 내용으로 회사가 서비스 성격에 부합하지 않는다고 판단하는 경우<br>

	- 회사에서 규정한 게시물 원칙에 어긋나거나, 게시판 성격에 부합하지 않는 경우<br>

	- 기타 관계법령에 위배된다고 판단되는 경우</p>

	④ 회원이 게시한 게시물의 저작권은 게시한 회원에게 귀속됩니다. 단,“당사”는 서비스의 운영, 전시, 전송, 배포, 홍보의 목적으로 회원의 별도 허락 없이 무상으로 저작권법에 규정하는 공정한 관행에 합치되게 회원의 게시물을 사용할 수 있습니다.<br>

	④ “당사”는 전항 이외의 방법으로 회원의 게시물을 이용하고자 하는 경우, 전화, 팩스, 전자우편 등의 방법을 통해 사전에 회원의 동의를 얻어야 합니다.<br>

	⑥ 회원이 이용계약 해지를 한 경우 타인에 의해 보관, 담기 등으로 재게시 되거나 복제된 게시물과 타인의 게시물과 결합되어 제공되는 게시물, 공용 게시판에 등록된 게시물 등은 삭제되지 않습니다.
	</dd>
	 

	 

	<dt>제 24 조(분쟁해결)</dt>

	 

	<dd>① "당사"는 이용자가 제기하는 정당한 의견이나 불만을 반영하고 그 피해를 보상처리하기 위하여 피해보상처리기구를 설치•운영합니다.<br>

	② "당사"는 이용자로부터 제출되는 불만사항 및 의견은 우선적으로 그 사항을 처리합니다. 다만, 신속한 처리가 곤란한 경우에는 이용자에게 그 사유와 처리일정을 즉시 통보해 드립니다.<br>

	③ "당사"와 이용자 간에 발생한 전자상거래 분쟁과 관련하여 이용자의 피해구제신청이 있는 경우에는 공정거래위원회 또는 시•도지사가 의뢰하는 분쟁조정기관의 조정에 따를 수 있습니다.
	</dd>
	 

	 

	<dt>제 25 조(재판권 및 준거법)</dt>

	 

	<dd>① "당사"와 이용자 간에 발생한 전자상거래 분쟁에 관한 소송은 “당사”가 소재하는 법원의 전속관할로 합니다.<br>

	② "당사"와 이용자 간에 제기된 전자상거래 소송에는 한국법을 적용합니다.
	</dd>
	 

	 

	<dt>제 26 조(특별규정)</dt>

	 

	<dd>① 당 약관에 명시되지 않은 사항은 전자거래기본법, 전자서명법, 전자상거래 등에서의 소비자보호에 관한 법률, 기타 관련법령의 규정 및 국내(외)여행표준약관 등에 의합니다.</dd>

	</dl>
</div>`;

const PRIVACY_HTML = `
<div class="agree">
  우노트래블(이하 ‘당사’)는 고객님의 개인정보취급(처리)방침을 매우 중요시하며, 정보통신망 이용촉진 및 정보보호에 관한 법률 및 개인정보보호법을 준수하고 있습니다. 당사는 개인정보취급(처리)방침을 통하여 개인정보가 어떠한 용도와 방식으로 이용되고 있으며 개인정보보호를 위해 어떠한 조치가 취해지고 있는지 알려드립니다.<br><br>
  * 개인정보의 수집, 제공 및 활용에 동의하지 않을 권리가 있으며, 미동의 시 회원가입 및 여행서비스의 제공이 제한됩니다.<br><br>
  <strong>1. 개인정보의 수집 및 이용 목적</strong><br>
  1) 당사는 여행상품 예약 및 여행 관련 서비스 제공 등의 업무처리를 위하여 고객으로부터 최소한의 필수정보를 수집합니다.<br>
  2) 제공하신 모든 정보는 상기 목적에 필요한 용도 이외로는 사용되지 않으며, 수집 정보의 범위나 사용목적이 변경될 시에는 고객님께 사전 동의를 구합니다.<br>
  3) 수집한 개인정보는 서비스 제공 계약 이행, 예약·상담·결제·본인 인증, 회원 관리, 민원 처리, 고지사항 전달, 마케팅 및 광고성 정보 전달 등에 사용됩니다.<br><br>
  <strong>2. 개인정보 수집 항목 및 수집방법</strong><br>
  당사는 홈페이지, 전화, 팩스 및 상품 판매 과정에서 본인 확인과 서비스 이용에 필요한 최소한의 개인정보를 수집합니다. 종교, 인종, 사상, 정치적 성향, 건강상태, 성생활정보 등 민감정보는 수집하지 않습니다.<br><br>
  <table>
    <colgroup><col style="width:18%"><col style="width:auto"><col style="width:34%"></colgroup>
    <tr><th>구분</th><th>개인정보 항목</th><th>용도</th></tr>
    <tr><td>회원 서비스 가입</td><td>아이디, 비밀번호, 성명(국문/영문), 이메일, 휴대전화번호</td><td>회원 서비스 제공 및 본인인증</td></tr>
    <tr><td>투어상품 예약 진행</td><td>성명(국문/영문), 휴대전화번호, 이메일, 생년월일, 주소</td><td>투어상품 예약 및 상담 / 쿠폰 / 포인트 / 경품배송</td></tr>
    <tr><td>여행상품 예약 및 견적</td><td>예약자 성명, 휴대전화번호, 이메일 / 여행자 성명, 휴대전화번호, 이메일, 성별, 여권소지여부 및 여권정보, 주소</td><td>여행상품 예약 및 상담 / 예약 및 출국 가능 여부 파악 / 여행자보험 가입</td></tr>
    <tr><td>결제 진행시</td><td>성명, 신용카드번호, 유효기간 등 결제정보</td><td>대금결제 / 정산</td></tr>
  </table><br>
  <strong>3. 개인정보의 이용, 보유기간 및 파기</strong><br>
  수집목적이 달성되거나 회원탈퇴 요청이 있는 경우 개인정보는 재생할 수 없는 방법으로 파기합니다. 단, 관계법령에 따라 계약 또는 청약철회 기록, 대금결제 및 재화 공급 기록은 5년, 소비자 불만 또는 분쟁처리 기록은 3년간 보관합니다.<br><br>
  <strong>4. 개인정보 제공 및 공유</strong><br>
  당사는 고객님의 동의가 있거나 관련 법령에 따른 경우를 제외하고 고지한 범위를 넘어 타 기업·기관에 개인정보를 제공하지 않습니다. 여행상품 예약, 항공·숙박·현지 행사 진행, 본인인증, 결제정산 등 서비스 제공에 필요한 경우에 한하여 관련 업체에 필요한 정보를 제공할 수 있습니다.<br><br>
  <strong>5. 개인정보 취급 위탁</strong><br>
  당사는 고객 편의 서비스를 원활하게 제공하기 위해 일부 업무를 전문업체에 위탁할 수 있으며, 위탁업무의 내용이나 수탁자가 변경될 경우 개인정보 처리방침을 통하여 공개합니다.<br><br>
  <strong>6. 개인정보의 열람 및 정정</strong><br>
  고객님은 홈페이지 마이페이지의 회원정보 수정을 통해 개인정보를 열람 또는 정정할 수 있으며, 당사는 개인정보에 대한 열람·정정 요구에 성실하게 대응합니다.<br><br>
  <strong>7. 개인정보보호를 위한 기술 및 관리대책</strong><br>
  당사는 비밀번호 보호, 데이터 암호화, 백신프로그램, 보안장치, 접근권한 최소화, 개인정보 취급자 교육 등 개인정보 보호를 위한 기술적·관리적 조치를 시행합니다.<br><br>
  <strong>8. 개인정보 관련 문의</strong><br>
  개인정보 침해신고센터 전화 118 / 이메일 118@kisa.or.kr 등을 통해 개인정보 관련 상담을 받을 수 있습니다.
</div>
`;

function navigateTo(path: string) {
  if (typeof window === "undefined") return;

  window.history.pushState({}, "", path);
  window.dispatchEvent(new Event("unotravel:navigate"));
}

export default function RegisterAgreement() {
  const [agreeTerms, setAgreeTerms] = useState(false);
  const [agreePrivacy, setAgreePrivacy] = useState(false);
  const [notice, setNotice] = useState("");

  const canContinue = useMemo(() => agreeTerms && agreePrivacy, [agreeTerms, agreePrivacy]);

  function handleAllAgree(checked: boolean) {
    setAgreeTerms(checked);
    setAgreePrivacy(checked);
    if (checked) setNotice("");
  }

  function handleContinue() {
    if (!canContinue) {
      setNotice("필수 약관에 모두 동의해 주세요.");
      return;
    }

    /*
      Agreement Submit Hook
      ------------------------------------------
      실제 백엔드 연동 시 약관 동의 상태를 가입 세션 또는 API로 전달한다.
      현재는 프론트 UI/동선 확인용으로 /register/form으로 이동한다.
    */
    setNotice("");
    navigateTo("/register/form");
  }

  return (
    <main className="agreement-page-shell">
      <style>{STYLE}</style>

      <section className="agreement-page-inner" aria-label="우노트래블 회원가입 약관 동의">
        <aside className="agreement-aside" aria-label="회원가입 현재 단계">
          <button
            type="button"
            className="agreement-back-button"
            aria-label="회원가입 시작 페이지로 이동"
            onClick={() => navigateTo("/register")}
          >
            ←
          </button>

          <div className="agreement-aside-center">
            <div className="agreement-kicker">REGISTER STEP 01</div>

            <h1 className="agreement-title">
              필수 약관 동의
            </h1>

            <p className="agreement-description">
              회원가입 전 반드시 확인해야 하는 두 가지 문서입니다.
              내용을 확인한 뒤 필수 항목에 동의해 주세요.
            </p>

            <div className="agreement-aside-consent">
              <button
                            type="button"
                            className={`agreement-all-check ${canContinue ? "is-checked" : ""}`}
                            aria-pressed={canContinue}
                            onClick={() => handleAllAgree(!canContinue)}
                          >
                            <span aria-hidden="true" />
                            전체 동의
                          </button>
            </div>
          </div>

          <div className="agreement-step-index" aria-hidden="true">
            <span>01</span>
            <strong>AGREEMENT</strong>
          </div>
        </aside>

        <div className="agreement-document-area">
          <div className="agreement-document-header">
            <div>
              <span>DOCUMENTS</span>
              <strong>필수 약관 동의</strong>
            </div>

            
          </div>

          <div className="agreement-documents">
            <section className="agreement-document">
              <div className="agreement-document-meta">
                <div>
                  <span>01</span>
                  <strong>서비스 이용 약관</strong>
                  <p>우노트래블 서비스 이용을 위한 필수 약관입니다.</p>
                </div>

                <button
                  type="button"
                  className={`agreement-check ${agreeTerms ? "is-checked" : ""}`}
                  aria-pressed={agreeTerms}
                  onClick={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    setAgreeTerms((prev) => !prev);
                    if (notice) setNotice("");
                  }}
                >
                  <span aria-hidden="true" />
                  동의
                </button>
              </div>

              <div
                className="agreement-preview agreement-preview-terms"
                tabIndex={0}
                dangerouslySetInnerHTML={{ __html: TERMS_HTML }}
              />
            </section>

            <section className="agreement-document">
              <div className="agreement-document-meta">
                <div>
                  <span>02</span>
                  <strong>개인정보 수집 및 이용 동의</strong>
                  <p>회원 식별과 예약 관리를 위한 필수 항목입니다.</p>
                </div>

                <button
                  type="button"
                  className={`agreement-check ${agreePrivacy ? "is-checked" : ""}`}
                  aria-pressed={agreePrivacy}
                  onClick={(event) => {
                    event.preventDefault();
                    event.stopPropagation();
                    setAgreePrivacy((prev) => !prev);
                    if (notice) setNotice("");
                  }}
                >
                  <span aria-hidden="true" />
                  동의
                </button>
              </div>

              <div
                className="agreement-preview agreement-preview-privacy"
                tabIndex={0}
                dangerouslySetInnerHTML={{ __html: PRIVACY_HTML }}
              />
            </section>
          </div>

          {notice && (
            <p className="agreement-notice" role="status" aria-live="polite">
              <span aria-hidden="true" />
              {notice}
            </p>
          )}

          <div className="agreement-actions">
            <button type="button" className="agreement-prev" onClick={() => navigateTo("/register")}>
              이전
            </button>

            <button
              type="button"
              className={`agreement-submit ${canContinue ? "is-ready" : ""}`}
              onClick={handleContinue}
            >
              <span>다음 단계</span>
              <span aria-hidden="true">→</span>
            </button>
          </div>
        </div>
      </section>
    </main>
  );
}

const STYLE = `
  .agreement-page-shell {
    width: 100%;
    min-width: 1024px;
    min-height: 100vh;
    background: #ffffff;
    color: #111111;
    overflow-x: hidden;
    overflow-y: auto;
  }

  .agreement-page-inner {
    width: 100%;
    min-height: 100vh;
    display: grid;
    grid-template-columns: minmax(360px, 34vw) minmax(620px, 1fr);
    gap: 0;
    padding: 14px;
    box-sizing: border-box;
    background: #ffffff;
    align-items: start;
  }

  .agreement-aside {
    position: sticky;
    top: 14px;
    min-height: calc(100vh - 28px);
    padding: 34px 44px 40px;
    box-sizing: border-box;
    display: grid;
    grid-template-rows: auto 1fr auto;
    border-right: 1px solid rgba(17, 17, 17, 0.12);
    background: #ffffff;
  }

  .agreement-back-button {
    width: 34px;
    height: 34px;
    border: 0;
    background: transparent;
    color: rgba(17, 17, 17, 0.48);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: flex-start;
    font-family: var(--font-en);
    font-size: 26px;
    line-height: 1;
    transition: color 0.2s ease, transform 0.2s ease;
  }

  .agreement-back-button:hover {
    color: #111111;
    transform: translateX(-2px);
  }

  .agreement-aside-center {
    width: min(360px, 100%);
    align-self: center;
  }

  .agreement-kicker {
    margin: 0 0 34px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.31em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.36);
  }

  .agreement-title {
    margin: 0;
    font-family: var(--font-ko);
    font-size: clamp(36px, 3.6vw, 54px);
    line-height: 1.1;
    letter-spacing: -0.075em;
    font-weight: 620;
    color: #111111;
    word-break: keep-all;
  }

  .agreement-description {
    max-width: 330px;
    margin: 36px 0 0;
    font-family: var(--font-ko);
    font-size: 13px;
    line-height: 1.86;
    letter-spacing: -0.04em;
    font-weight: 500;
    color: rgba(17, 17, 17, 0.56);
    word-break: keep-all;
  }


  .agreement-aside-consent {
    margin-top: 34px;
    padding-top: 24px;
    border-top: 1px solid rgba(17, 17, 17, 0.16);
  }

  .agreement-aside-consent .agreement-all-check {
    height: 46px;
    padding: 0 16px;
    border: 1px solid rgba(17, 17, 17, 0.14);
    background: transparent;
  }

  .agreement-aside-consent .agreement-all-check:hover {
    border-color: rgba(17, 17, 17, 0.32);
  }

  .agreement-step-index {
    display: grid;
    gap: 8px;
    font-family: var(--font-en);
    line-height: 1;
  }

  .agreement-step-index span {
    font-size: 10px;
    letter-spacing: 0.22em;
    font-weight: 760;
    color: #111111;
  }

  .agreement-step-index span::after {
    content: "";
    display: block;
    width: 22px;
    height: 2px;
    margin-top: 16px;
    background: #fcc800;
  }

  .agreement-step-index strong {
    margin-top: 10px;
    font-size: 10px;
    letter-spacing: 0.18em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.38);
  }

  .agreement-document-area {
    min-height: calc(100vh - 28px);
    padding: 42px 54px 40px;
    box-sizing: border-box;
    display: grid;
    grid-template-rows: auto auto auto auto;
    background: #ffffff;
    min-width: 0;
  }

  .agreement-document-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 40px;
    padding-bottom: 28px;
    border-bottom: 1px solid rgba(17, 17, 17, 0.22);
  }

  .agreement-document-header div {
    display: grid;
    gap: 9px;
  }

  .agreement-document-header span {
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.26em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.36);
  }

  .agreement-document-header strong {
    font-family: var(--font-ko);
    font-size: 20px;
    line-height: 1.2;
    letter-spacing: -0.055em;
    font-weight: 640;
    color: #111111;
  }

  .agreement-documents {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 18px;
    align-self: start;
    padding: 24px 0 0;
    min-height: 0;
  }

  .agreement-document {
    min-height: 0;
    min-width: 0;
    display: flex;
    flex-direction: column;
    border-top: 1px solid rgba(17, 17, 17, 0.12);
  }

  .agreement-document-meta {
    min-height: 122px;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 22px;
    align-items: start;
    padding: 20px 0 22px;
    box-sizing: border-box;
  }

  .agreement-document-meta div > span {
    display: block;
    margin-bottom: 18px;
    font-family: var(--font-en);
    font-size: 10px;
    line-height: 1;
    letter-spacing: 0.22em;
    font-weight: 760;
    color: rgba(17, 17, 17, 0.34);
  }

  .agreement-document-meta strong {
    display: block;
    font-family: var(--font-ko);
    font-size: 15px;
    line-height: 1.2;
    font-weight: 650;
    letter-spacing: -0.045em;
    color: #111111;
  }

  .agreement-document-meta p {
    max-width: 320px;
    margin: 10px 0 0;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.58;
    font-weight: 500;
    letter-spacing: -0.04em;
    color: rgba(17, 17, 17, 0.52);
    word-break: keep-all;
  }

  .agreement-preview {
    display: block;
    height: 58vh;
    min-height: 460px;
    max-height: 680px;
    padding: 26px 26px 30px;
    box-sizing: border-box;
    background: rgba(17, 17, 17, 0.025);
    border: 1px solid rgba(17, 17, 17, 0.08);
    overflow-y: scroll;
    overflow-x: hidden;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
    font-family: var(--font-ko);
    font-size: 12px;
    font-weight: 500;
    line-height: 1.78;
    letter-spacing: -0.04em;
    color: rgba(17, 17, 17, 0.62);
    word-break: keep-all;
    scrollbar-width: thin;
  }

  .agreement-preview-terms,
  .agreement-preview-privacy {
    overflow-y: scroll;
  }

  .agreement-preview::-webkit-scrollbar {
    width: 6px;
  }

  .agreement-preview::-webkit-scrollbar-thumb {
    background: rgba(17, 17, 17, 0.18);
  }

  .agreement-preview .agree dl,
  .agreement-preview .agree dt,
  .agreement-preview .agree dd,
  .agreement-preview .agree p {
    margin: 0;
    padding: 0;
  }

  .agreement-preview .agree dt {
    margin: 18px 0 7px;
    font-weight: 700;
    color: rgba(17, 17, 17, 0.86);
  }

  .agreement-preview .agree dd {
    margin-bottom: 14px;
  }

  .agreement-preview .agree strong {
    display: inline-block;
    margin: 14px 0 6px;
    font-size: 13px;
    color: #111111;
  }

  .agreement-preview table {
    width: 100%;
    margin: 12px 0;
    border-collapse: collapse;
    font-size: 11px;
    line-height: 1.5;
  }

  .agreement-preview th,
  .agreement-preview td {
    border: 1px solid rgba(17, 17, 17, 0.14);
    padding: 8px;
    vertical-align: top;
  }

  .agreement-preview th {
    background: rgba(17, 17, 17, 0.04);
    color: #111111;
  }

  .agreement-check,
  .agreement-all-check {
    border: 0;
    background: transparent;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1;
    font-weight: 620;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.66);
    white-space: nowrap;
    transition: color 0.2s ease;
  }

  .agreement-check:hover,
  .agreement-all-check:hover {
    color: #111111;
  }

  .agreement-check span,
  .agreement-all-check span {
    width: 18px;
    height: 18px;
    border: 1px solid rgba(17, 17, 17, 0.24);
    background: #ffffff;
    box-sizing: border-box;
    position: relative;
    transition: background 0.18s ease, border-color 0.18s ease;
  }

  .agreement-check.is-checked span,
  .agreement-all-check.is-checked span {
    background: #fcc800;
    border-color: #fcc800;
  }

  .agreement-check.is-checked span::after,
  .agreement-all-check.is-checked span::after {
    content: "";
    position: absolute;
    left: 5px;
    top: 2px;
    width: 5px;
    height: 9px;
    border: solid #111111;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
  }

  .agreement-check.is-checked,
  .agreement-all-check.is-checked {
    color: #111111;
  }

  .agreement-notice {
    position: relative;
    margin: 18px 0 0;
    min-height: 42px;
    border: 1px solid rgba(17, 17, 17, 0.13);
    background: rgba(17, 17, 17, 0.032);
    padding: 13px 14px 13px 42px;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 12px;
    line-height: 1.45;
    font-weight: 560;
    letter-spacing: -0.035em;
    color: rgba(17, 17, 17, 0.72);
    animation: agreementNoticeIn 0.24s ease both;
  }

  .agreement-notice span {
    position: absolute;
    left: 14px;
    top: 50%;
    width: 16px;
    height: 16px;
    transform: translateY(-50%);
    border-radius: 999px;
    background: #111111;
  }

  .agreement-notice span::before,
  .agreement-notice span::after {
    content: "";
    position: absolute;
    left: 50%;
    background: #ffffff;
    transform: translateX(-50%);
  }

  .agreement-notice span::before {
    top: 3px;
    width: 1px;
    height: 6px;
  }

  .agreement-notice span::after {
    bottom: 3px;
    width: 2px;
    height: 2px;
    border-radius: 999px;
  }

  .agreement-actions {
    display: grid;
    grid-template-columns: 160px 1fr;
    gap: 12px;
    margin-top: 22px;
  }

  .agreement-prev,
  .agreement-submit {
    height: 56px;
    cursor: pointer;
    box-sizing: border-box;
    font-family: var(--font-ko);
    font-size: 14px;
    font-weight: 650;
    letter-spacing: -0.025em;
    transition:
      transform 0.22s ease,
      background 0.22s ease,
      color 0.22s ease,
      border-color 0.22s ease;
  }

  .agreement-prev {
    border: 1px solid rgba(17, 17, 17, 0.16);
    background: #ffffff;
    color: rgba(17, 17, 17, 0.68);
  }

  .agreement-prev:hover {
    border-color: rgba(17, 17, 17, 0.38);
    color: #111111;
    transform: translateY(-1px);
  }

  .agreement-submit {
    border: 0;
    border-radius: 2px;
    background: #111111;
    color: #ffffff;
    display: grid;
    grid-template-columns: 1fr auto;
    align-items: center;
    padding: 0 22px;
  }

  .agreement-submit:hover {
    background: #fcc800;
    color: #111111;
    transform: translateY(-1px);
  }

  .agreement-submit:not(.is-ready) {
    background: rgba(17, 17, 17, 0.18);
    color: rgba(255, 255, 255, 0.82);
  }

  .agreement-submit:not(.is-ready):hover {
    background: #111111;
    color: #ffffff;
  }

  @keyframes agreementNoticeIn {
    from {
      opacity: 0;
      transform: translateY(-4px);
    }
    to {
      opacity: 1;
      transform: translateY(0);
    }
  }

  @media (max-width: 1180px) {
    .agreement-page-inner {
      grid-template-columns: minmax(330px, 34vw) minmax(620px, 1fr);
    }

    .agreement-aside {
      padding-left: 34px;
      padding-right: 34px;
    }

    .agreement-document-area {
      padding-left: 38px;
      padding-right: 38px;
    }

    .agreement-documents {
      gap: 14px;
    }
  }
`;
