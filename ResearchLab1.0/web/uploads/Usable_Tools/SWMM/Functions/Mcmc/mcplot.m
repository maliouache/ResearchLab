function mcplot(chain,names)
n=size(chain,2)-1;
rs=RSD(chain(:,:));
r=R_calcu(chain(:,1:n));
if n==7
x1=0.18;x2=0.865;y1=0.1745;y2=0.902;
dx=(x2-x1)/(n-1);
dy=(y2-y1)/(n-1);
end
if n==6
x1=0.195;x2=0.87;y1=0.1925;y2=0.904;
dx=(x2-x1)/(n-1);
dy=(y2-y1)/(n-1);
end

if n==3
x1=0.295;x2=0.87;y1=0.300;y2=0.904;
dx=(x2-x1)/(n-1);
dy=(y2-y1)/(n-1);
end

if n==2
x1=0.40;x2=0.82;y1=0.4120;y2=0.904;
dx=(x2-x1)/(n-1);
dy=(y2-y1)/(n-1);
end

for i=1:1:n-1
   for j=i+1:1:n
       subplot(n,n,(j-1)*n+i);
       plot(chain(:,i),chain(:,j),'.');
       xlim([0.95*min(chain(:,i)) 1.05*max(chain(:,i))]);
       ylim([0.95*min(chain(:,j)) 1.05*max(chain(:,j))]);
       if i ~=1
           set(gca,'ytick',[]);
       end
       if j ~=n
           set(gca,'xtick',[]);
       end
       set(gca,'FontSize',18,'FontName','Times New Roman');
a=annotation('textbox',[x1+(i-1)*dx, y2-(j-1)*dy, 0.1, 0.01],'String',['r=' char(vpa(r(j-1,i),3)) ],'FitBoxToText','on','FaceAlpha',0.33);
a.BackgroundColor='white';
a.LineWidth=0.001;
a.EdgeColor='white';
a.FontName='Times New Roman';
a.FontWeight='bold';
a.FontSize=18;

   end
end
%Hist
for i=1:1:n
subplot(n,n,n*(i-1)+i);
histogram(chain(:,i),25);
       if i ~=n
           set(gca,'xtick',[]);
       end
       set(gca,'FontSize',18,'FontName','Times New Roman');
        xlim([0.95*min(chain(:,i)) 1.05*max(chain(:,i))]);
        a=annotation('textbox',[x1+(i-1)*dx-0.015, y2-(i-1)*dy, 0.1, 0.01],'String',['RSD=' char(vpa(rs(i),3)) ],'FitBoxToText','on','FaceAlpha',0.33);
a.BackgroundColor='white';
a.LineWidth=0.001;
a.EdgeColor='white';
a.FontName='Times New Roman';
a.FontWeight='bold';
a.FontSize=18;

end
%axis
for i=1:1:n
    subplot(n,n,n*(n-1)+i);
xlabel(names{i},'FontSize',18,'FontName','Times New Roman');
subplot(n,n,n*(i-1)+1);
ylabel(names{i},'FontSize',18,'FontName','Times New Roman');
% set(gca,'FontSize',10,'FontName','Times New Roman');
end
set(gcf,'unit','normalized','position',[0.0,0,1,1.2]);



end
