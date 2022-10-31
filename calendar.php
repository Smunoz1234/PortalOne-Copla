<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("includes/cabecera.php"); ?>
    <title>Calendario</title>
    
</head>
<body>
    <div id="calendar"></div>
</body>


<script>
    $(function() {
  // agarre el enfoque para que el enlace a continuación funcione
  $(window).focus();

  // se utiliza para rastrear si el usuario está presionando la tecla de control
  let ctrlIsPressed = false;

  function setEventsCopyable(isCopyable) {
    ctrlIsPressed = !ctrlIsPressed;
    $("#calendar").fullCalendar("option", "eventStartEditable", !isCopyable);
    $(".fc-event").draggable("option", "disabled", !isCopyable);
  }

  // establecer eventos copiables si el usuario mantiene presionada la tecla de control
  $(document).keydown(function(e) {
    if (e.ctrlKey && !ctrlIsPressed) {
      setEventsCopyable(true);
    }
  });

  // si se ha liberado el control, evitar que los eventos se puedan copiar
  $(document).keyup(function(e) {
    if (ctrlIsPressed) {
      setEventsCopyable(false);
    }
  });

  let $calendar = $("#calendar").fullCalendar({
    // https://fullcalendar.io/scheduler/license
    schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
    header: { center: 'basicWeek,timelineMonth' },
    defaultView: "timelineMonth",
    defaultDate: "2018-04-07",
    resources: [
      { id: "a", title: "Room A" },
      { id: "b", title: "Room B", eventColor: "green" },
      { id: "c", title: "Room C", eventColor: "orange" },
      { id: "d", title: "Room D", eventColor: "red" }
    ],
    events: [
      {
        id: "1",
        resourceId: "a",
        start: "2018-04-06",
        end: "2018-04-08",
        title: "event 1"
      },
      {
        id: "2",
        resourceId: "a",
        start: "2018-04-07T09:00:00",
        end: "2018-04-07T14:00:00",
        title: "event 2"
      },
      {
        id: "3",
        resourceId: "b",
        start: "2018-04-07T12:00:00",
        end: "2018-04-08T06:00:00",
        title: "event 3"
      },
      {
        id: "4",
        resourceId: "c",
        start: "2018-04-07T07:30:00",
        end: "2018-04-07T09:30:00",
        title: "event 4"
      },
      {
        id: "5",
        resourceId: "d",
        start: "2018-04-07T10:00:00",
        end: "2018-04-07T15:00:00",
        title: "event 5"
      }
    ],
    editable: true,
    droppable: true,
    eventAfterAllRender(event, element, view) {
      // hacer que todos los eventos se puedan copiar usando jQuery UI arrastrable
      $(".fc-event").each(function() {
        const $event = $(this);

        // almacenar datos para que se puedan copiar en la función drop
        const event = $event.data("fcSeg").footprint.eventDef;
        $event.data("eventObj", event);

        // hacer que el evento se pueda arrastrar usando jQuery UI
        $event.draggable({
          disabled: true,
          helper: "clone",
          revert: true,
          revertDuration: 0,
          zIndex: 999,
          stop(event, ui) {
            // cuando se detiene el arrastre de un evento copiado, debemos configurarlos
            // copiable de nuevo si la tecla de control aún se mantiene presionada
            if (ctrlIsPressed) {
              setEventsCopyable(true);
            }
          }
        });
      });
    },
    drop: function(date, jsEvent, ui, resourceId) {
      const droppedEvent = $(this).data("eventObj");
      const origStartDate = droppedEvent.dateProfile.start;
      const origEndDate = droppedEvent.dateProfile.end;

      // Establezca la fecha de inicio en la nueva fecha con la hora original
      let startDate = moment(date);
      startDate.set({
        hour: origStartDate.get("hour"),
        minute: origStartDate.get("minute"),
        second: origStartDate.get("second")
      });

      const endDate = moment(date);
      
      // Si las fechas originales fueron en días diferentes, tenemos que calcular la nueva fecha de finalización.
      if (!origStartDate.isSame(origEndDate, "d")) {
        endDate.add(
          droppedEvent.dateProfile.end.diff(
            droppedEvent.dateProfile.start,
            "d"
          ),
          "d"
        );
      }

      endDate.set({
        hour: origEndDate.get("hour"),
        minute: origEndDate.get("minute"),
        second: origEndDate.get("second")
      });

      $calendar.fullCalendar(
        "renderEvent",
        {
          resourceId,
          title: droppedEvent.title,
          start: startDate,
          end: endDate
        },
        true
      );
    }
  });
});
</script>
</html>